<?php
    // --- 1. XỬ LÝ LỌC THỜI GIAN ---
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 7; // Mặc định 7 ngày
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    $where_time = "";
    $label_time = "";

    if ($start_date != '' && $end_date != '') {
        $where_time = "DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
        $label_time = "Từ " . date('d/m/Y', strtotime($start_date)) . " đến " . date('d/m/Y', strtotime($end_date));
        $days = 0; // Tắt active nút nhanh
    } else {
        $where_time = "created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)";
        $label_time = "Trong $days ngày qua";
    }

    // --- 2. TRUY VẤN CÁC CHỈ SỐ KPI (THỐNG KÊ TỔNG QUAN) ---
    // KPI 1: Doanh thu (Đơn hàng phải ở trạng thái 'done')
    $sql_revenue = "SELECT SUM(total_amount) as total_rev FROM tbl_orders WHERE status = 'done' AND $where_time";
    $rev_result = mysqli_query($conn, $sql_revenue);
    $total_revenue = mysqli_fetch_assoc($rev_result)['total_rev'] ?? 0;

    // KPI 2: Số lượng đơn hàng
    $sql_orders = "SELECT COUNT(id) as total_ord FROM tbl_orders WHERE $where_time";
    $ord_result = mysqli_query($conn, $sql_orders);
    $total_orders = mysqli_fetch_assoc($ord_result)['total_ord'] ?? 0;

    // KPI 3: Yêu cầu dịch vụ mới (tbl_services)
    $sql_services = "SELECT COUNT(id) as total_srv FROM tbl_services WHERE $where_time";
    $srv_result = mysqli_query($conn, $sql_services);
    $total_services = mysqli_fetch_assoc($srv_result)['total_srv'] ?? 0;

    // KPI 4: Cảnh báo hết hàng (tbl_products)
    $sql_stock = "SELECT COUNT(id) as out_stock FROM tbl_products WHERE stock <= 5 AND status = 1";
    $stock_result = mysqli_query($conn, $sql_stock);
    $out_of_stock = mysqli_fetch_assoc($stock_result)['out_stock'] ?? 0;

    // KPI 5: Yêu cầu liên hệ (tbl_contacts)
    $sql_contacts = "SELECT COUNT(id) as total_ct FROM tbl_contacts WHERE $where_time";
    $ct_result = mysqli_query($conn, $sql_contacts);
    $total_contacts = mysqli_fetch_assoc($ct_result)['total_ct'] ?? 0;

    // --- 3. TRUY VẤN DỮ LIỆU VẼ BIỂU ĐỒ (CHART.JS) ---
    // Biểu đồ 1: Doanh thu theo từng ngày
    $sql_chart_rev = "SELECT DATE(created_at) as date_val, SUM(total_amount) as daily_rev 
                      FROM tbl_orders 
                      WHERE status = 'done' AND $where_time 
                      GROUP BY DATE(created_at) 
                      ORDER BY DATE(created_at) ASC";
    $chart_rev_res = mysqli_query($conn, $sql_chart_rev);
    
    $dates = [];
    $revenues = [];
    while($row = mysqli_fetch_assoc($chart_rev_res)){
        $dates[] = date('d/m', strtotime($row['date_val']));
        $revenues[] = (float)$row['daily_rev'];
    }

    // Biểu đồ 2: Tỷ lệ trạng thái đơn hàng (Doughnut Chart)
    $sql_chart_status = "SELECT status, COUNT(id) as count_status 
                         FROM tbl_orders 
                         WHERE $where_time 
                         GROUP BY status";
    $chart_st_res = mysqli_query($conn, $sql_chart_status);
    
    $st_labels = [];
    $st_data = [];
    $st_colors = [];
    
    $status_map = [
        'new' => ['Mới nhận', '#3b82f6'],       // Xanh dương
        'confirmed' => ['Đã xác nhận', '#8b5cf6'], // Tím
        'shipping' => ['Đang giao', '#f59e0b'],    // Vàng cam
        'done' => ['Hoàn thành', '#10b981'],       // Xanh lá
        'cancelled' => ['Đã hủy', '#ef4444']       // Đỏ
    ];

    while($row = mysqli_fetch_assoc($chart_st_res)){
        $st_key = $row['status'];
        $st_labels[] = $status_map[$st_key][0] ?? $st_key;
        $st_data[] = (int)$row['count_status'];
        $st_colors[] = $status_map[$st_key][1] ?? '#cbd5e1';
    }

    // --- 4. DANH SÁCH ĐƠN HÀNG MỚI NHẤT ---
    $sql_recent_orders = "SELECT id, order_code, customer_name, total_amount, status, created_at 
                          FROM tbl_orders 
                          ORDER BY id DESC LIMIT 6";
    $recent_orders = mysqli_query($conn, $sql_recent_orders);
?>

<style>
    .lvc-dash-wrapper { font-family: 'Segoe UI', sans-serif; color: #1e293b; padding-bottom: 40px; }
    
    /* Header & Filters */
    .lvc-dash-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
    .lvc-dash-title { font-size: 1.8rem; font-weight: 800; color: #0b2447; margin: 0; }
    .lvc-dash-subtitle { font-size: 0.95rem; color: #64748b; font-weight: 500; }
    
    .lvc-dash-filter-box { background: #fff; padding: 10px 15px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;}
    .lvc-filter-btn { padding: 6px 14px; border-radius: 20px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: 0.2s; text-decoration: none; }
    .lvc-filter-btn:hover { background: #f1f5f9; }
    .lvc-filter-btn.active { background: #0b2447; color: #fff; border-color: #0b2447; }
    .lvc-filter-input { padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.85rem; outline: none; }
    
    /* KPI Cards */
    .lvc-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .lvc-kpi-card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; transition: transform 0.3s ease; }
    .lvc-kpi-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
    .lvc-kpi-icon { width: 60px; height: 60px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; flex-shrink: 0; }
    .lvc-kpi-info flex: 1; }
    .lvc-kpi-label { font-size: 0.9rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
    .lvc-kpi-value { font-size: 1.8rem; font-weight: 800; color: #0b2447; margin: 0; line-height: 1.2; }
    
    /* Chart Section */
    .lvc-chart-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; }
    .lvc-chart-card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
    .lvc-chart-title { font-size: 1.1rem; font-weight: 700; color: #0b2447; margin-bottom: 20px; display: flex; justify-content: space-between; }
    
    /* Recent Orders Table */
    .lvc-table-wrap { overflow-x: auto; }
    .lvc-table { width: 100%; border-collapse: collapse; }
    .lvc-table th { text-align: left; padding: 12px 15px; border-bottom: 2px solid #e2e8f0; color: #64748b; font-size: 0.85rem; text-transform: uppercase; }
    .lvc-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; vertical-align: middle; }
    .lvc-table tbody tr:hover { background: #f8fafc; }
    
    /* Badge Status */
    .lvc-badge-st { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }
    
    @media (max-width: 1024px) {
        .lvc-chart-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="wrapper lvc-dash-wrapper">
    <div class="lvc-dash-header">
        <div>
            <h2 class="lvc-dash-title"><i class="fa-solid fa-chart-line" style="color: #00875a;"></i> Dashboard Thống Kê</h2>
            <div class="lvc-dash-subtitle">Dữ liệu: <?= $label_time ?></div>
        </div>
        
        <div class="lvc-dash-filter-box">
            <span style="font-size: 0.85rem; font-weight: 600; color: #94a3b8;">Lọc nhanh:</span>
            <a href="?page=manage-dashboard&days=1" class="lvc-filter-btn <?= $days == 1 ? 'active' : '' ?>">Hôm nay</a>
            <a href="?page=manage-dashboard&days=3" class="lvc-filter-btn <?= $days == 3 ? 'active' : '' ?>">3 Ngày</a>
            <a href="?page=manage-dashboard&days=7" class="lvc-filter-btn <?= $days == 7 ? 'active' : '' ?>">7 Ngày</a>
            <a href="?page=manage-dashboard&days=10" class="lvc-filter-btn <?= $days == 10 ? 'active' : '' ?>">10 Ngày</a>
            <a href="?page=manage-dashboard&days=30" class="lvc-filter-btn <?= $days == 30 ? 'active' : '' ?>">30 Ngày</a>
            
            <form action="" method="GET" style="display: flex; gap: 5px; margin-left: 10px; border-left: 1px solid #e2e8f0; padding-left: 15px;">
                <input type="hidden" name="page" value="manage-dashboard">
                <input type="date" name="start_date" class="lvc-filter-input" value="<?= $start_date ?>" required>
                <span style="color: #94a3b8;">-</span>
                <input type="date" name="end_date" class="lvc-filter-input" value="<?= $end_date ?>" required>
                <button type="submit" class="lvc-filter-btn" style="background: #00875a; color: #fff; border:none;"><i class="fa-solid fa-filter"></i> Lọc</button>
            </form>
        </div>
    </div>

    <div class="lvc-kpi-grid">
        <div class="lvc-kpi-card">
            <div class="lvc-kpi-icon" style="background: #dcfce7; color: #059669;">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div class="lvc-kpi-info">
                <div class="lvc-kpi-label">Doanh thu thực tế</div>
                <div class="lvc-kpi-value"><?= number_format($total_revenue, 0, ',', '.') ?>đ</div>
            </div>
        </div>

        <div class="lvc-kpi-card">
            <div class="lvc-kpi-icon" style="background: #e0e7ff; color: #4338ca;">
                <i class="fa-solid fa-cart-flatbed"></i>
            </div>
            <div class="lvc-kpi-info">
                <div class="lvc-kpi-label">Đơn hàng mới</div>
                <div class="lvc-kpi-value"><?= number_format($total_orders) ?> <span style="font-size: 1rem; color:#64748b;">đơn</span></div>
            </div>
        </div>

        <div class="lvc-kpi-card">
            <div class="lvc-kpi-icon" style="background: #fef3c7; color: #d97706;">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div class="lvc-kpi-info">
                <div class="lvc-kpi-label">Yêu cầu DV & Khảo sát</div>
                <div class="lvc-kpi-value"><?= number_format($total_services) ?> <span style="font-size: 1rem; color:#64748b;">yêu cầu</span></div>
            </div>
        </div>

        <div class="lvc-kpi-card">
            <div class="lvc-kpi-icon" style="background: #dcfce7; color: #20bf6b;">
                <i class="fa-solid fa-phone-volume"></i>
            </div>
            <div class="lvc-kpi-info">
                <div class="lvc-kpi-label">Yêu cầu liên hệ</div>
                <div class="lvc-kpi-value"><?= number_format($total_contacts) ?> <span style="font-size: 1rem; color:#64748b;">yêu cầu</span></div>
            </div>
        </div>

        <div class="lvc-kpi-card">
            <div class="lvc-kpi-icon" style="background: #fee2e2; color: #dc2626;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="lvc-kpi-info">
                <div class="lvc-kpi-label">Cảnh báo sắp hết hàng</div>
                <div class="lvc-kpi-value" style="color: #dc2626;"><?= number_format($out_of_stock) ?> <span style="font-size: 1rem; color:#64748b;">sản phẩm</span></div>
            </div>
        </div>
    </div>

    <div class="lvc-chart-grid">
        <div class="lvc-chart-card">
            <div class="lvc-chart-title">
                Doanh thu hoàn thành theo thời gian
                <i class="fa-solid fa-chart-area" style="color: #94a3b8;"></i>
            </div>
            <div style="height: 300px; width: 100%;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="lvc-chart-card">
            <div class="lvc-chart-title">
                Tỷ lệ trạng thái đơn hàng
                <i class="fa-solid fa-chart-pie" style="color: #94a3b8;"></i>
            </div>
            <div style="height: 300px; width: 100%; display: flex; justify-content: center;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="lvc-chart-card">
        <div class="lvc-chart-title">
            Đơn hàng cập nhật gần đây
            <a href="?page=manage-order" style="font-size: 0.85rem; font-weight: 600; color: #00875a; text-decoration: none;">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="lvc-table-wrap">
            <table class="lvc-table">
                <thead>
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Ngày tạo</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($recent_orders) > 0): ?>
                        <?php while($ord = mysqli_fetch_assoc($recent_orders)): 
                            $st = $ord['status'];
                            $bg = $status_map[$st][1] ?? '#cbd5e1';
                            $lbl = $status_map[$st][0] ?? $st;
                        ?>
                        <tr>
                            <td style="font-weight: 700; color: #0b2447;">#<?= $ord['order_code'] ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($ord['customer_name']) ?></td>
                            <td style="color: #64748b;"><?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?></td>
                            <td style="font-weight: 700; color: #ef4444;"><?= number_format($ord['total_amount'], 0, ',', '.') ?>đ</td>
                            <td>
                                <span class="lvc-badge-st" style="background: <?= $bg ?>20; color: <?= $bg ?>;">
                                    <?= $lbl ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="?page=order-detail&id=<?= $ord['id'] ?>" class="lvc-filter-btn" style="background: #f1f5f9;"><i class="fa-solid fa-eye"></i> Xem</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: #94a3b8;">Chưa có đơn hàng nào trong khoảng thời gian này.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- 1. VẼ BIỂU ĐỒ DOANH THU (AREA CHART) ---
        const revCtx = document.getElementById('revenueChart').getContext('2d');
        
        // Dữ liệu PHP truyền sang JS
        const revLabels = <?= json_encode($dates) ?>;
        const revData = <?= json_encode($revenues) ?>;

        // Tạo hiệu ứng Gradient cho biểu đồ doanh thu (Màu xanh LVC Solar)
        let gradient = revCtx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(0, 135, 90, 0.4)'); // Xanh lá đậm
        gradient.addColorStop(1, 'rgba(0, 135, 90, 0.0)'); // Trong suốt

        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: revLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revData,
                    borderColor: '#00875a',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#00875a',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Làm cong đường line cho mượt
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.raw || 0;
                                return ' ' + new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            callback: function(value) {
                                if(value >= 1000000) return (value / 1000000) + 'Tr';
                                return value;
                            }
                        }
                    }
                }
            }
        });

        // --- 2. VẼ BIỂU ĐỒ TRẠNG THÁI (DOUGHNUT CHART) ---
        const stCtx = document.getElementById('statusChart').getContext('2d');
        
        const stLabels = <?= json_encode($st_labels) ?>;
        const stData = <?= json_encode($st_data) ?>;
        const stColors = <?= json_encode($st_colors) ?>;

        new Chart(stCtx, {
            type: 'doughnut',
            data: {
                labels: stLabels,
                datasets: [{
                    data: stData,
                    backgroundColor: stColors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 8 // Hiệu ứng đẩy ra khi hover
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%', // Độ rỗng ở giữa
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { padding: 20, font: { family: "'Segoe UI', sans-serif", size: 13 } }
                    }
                }
            }
        });
    });
</script>