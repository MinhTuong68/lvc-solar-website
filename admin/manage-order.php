<?php
    include("../classes/order.php");
    $order_obj = new Order($conn);

    if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['new_status'])) {
        $update_id = (int)$_GET['id'];
        $new_status = $_GET['new_status']; // Không cần escape ở đây vì trong hàm updateOrderStatus đã escape rồi
        
        // Gọi thẳng class
        $order_obj->updateOrderStatus($update_id, $new_status);
        
        $back_status = isset($_GET['status']) ? $_GET['status'] : 'all';
        echo "<script>window.location.href='index.php?page=manage-order&status=$back_status';</script>";
        exit;
    }
    $current_status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $current_search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $allOrders = $order_obj->getAllOrders($current_status, $current_search);
?>
<div class="wrapper">
    <div class="cs-page-wrapper">
        <div class="cs-header">
            <h1><i class="fa-solid fa-file-invoice-dollar" style="color: var(--amber); margin-right: 10px;"></i> Quản lý đơn hàng</h1>
            <span style="color: #64748b; font-size: 14px;">Kinh doanh / </span>
            <span style="font-size: 14px; font-weight: bold; color: var(--navy);">Đơn hàng</span>
        </div>
        <div class="cs-tabs">
            <a href="index.php?page=manage-order&status=all" class="cs-tab <?php echo ($current_status == 'all') ? 'active' : ''; ?>">Tất cả</a>
            <a href="index.php?page=manage-order&status=new" class="cs-tab <?php echo ($current_status == 'new') ? 'active' : ''; ?>">Chờ duyệt</a>
            <a href="index.php?page=manage-order&status=confirmed" class="cs-tab <?php echo ($current_status == 'confirmed') ? 'active' : ''; ?>">Đã xác nhận</a>
            <a href="index.php?page=manage-order&status=shipping" class="cs-tab <?php echo ($current_status == 'shipping') ? 'active' : ''; ?>">Đang giao</a>
            <a href="index.php?page=manage-order&status=done" class="cs-tab <?php echo ($current_status == 'done') ? 'active' : ''; ?>">Hoàn thành</a>
            <a href="index.php?page=manage-order&status=cancelled" class="cs-tab <?php echo ($current_status == 'cancelled') ? 'active' : ''; ?>">Đã hủy</a>
        </div>

        <div class="cs-filter-bar">
            <form action="" method="GET" class="filter-bar" style="display: flex; gap: 15px; width: 100%;">
                <input type="hidden" name="page" value="manage-order">
                <?php if ($current_status != 'all') { ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($current_status); ?>">
                <?php } ?>
                
                <input type="text" name="search" class="cs-select" placeholder="🔍 Tìm khách hàng, SĐT..." value="<?php echo htmlspecialchars($current_search); ?>">
                <button type="submit" class="cs-btn-filter"><i class="fa-solid fa-filter"></i> Lọc</button>
            </form>
        </div>

        <div class="cs-table-container">
            <table class="cs-table">
                <thead>
                    <tr>
                        <th>Mã ĐH / Ngày đặt</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th style="text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if (!empty($allOrders)) {
                            foreach ($allOrders as $order) {
                                // 1. Format Ngày giờ
                                $date_obj = new DateTime($order['created_at']);
                                $formatted_date = $date_obj->format('H:i - d/m/Y');
                                
                                // 2. Format Tiền tệ
                                $total_amount = number_format($order['total_amount'] ?? 0, 0, ',', '.') . ' đ';
                                $payment_method = ($order['payment_method'] === 'bank_transfer') ? 'Chuyển khoản' : 'COD';
                                $payment_icon = ($order['payment_method'] === 'bank_transfer') ? 'fa-building-columns' : 'fa-money-bill-wave';
                                $payment_status_text = ($order['payment_status'] === 'paid') ? '<span style="color:#059669;font-weight:bold;">Đã thanh toán</span>' : '<span style="color:#dc2626;">Chưa thanh toán</span>';

                                // 3. Set màu sắc Badge Trạng Thái
                                $status = $order['status'];
                               
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--navy);">ORD-<?= htmlspecialchars($order['id']) ?></div>
                                        <div style="font-size: 13px; color: #64748b; margin-top: 4px;"><?= $formatted_date ?></div>
                                    </td>
                                    <td>
                                        <span class="cs-customer-main"><?= htmlspecialchars($order['customer_name']) ?></span>
                                        <span class="cs-customer-sub"><i class="fa-solid fa-phone" style="font-size:11px;"></i> <?= htmlspecialchars($order['customer_phone']) ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #ef4444;"><?= $total_amount ?></div>
                                    </td>
                                    <td class="hide-on-mobile">
                                        <div style="font-size: 13px; color: #475569; margin-bottom: 3px;">
                                            <i class="fa-solid <?= $payment_icon ?>" style="color:#94a3b8;"></i> <?= $payment_method ?>
                                        </div>
                                        <div style="font-size: 12px;"><?= $payment_status_text ?></div>
                                    </td>
                                    <td>
                                        <select class="select-status <?= $status ?>" onchange="if(confirm('Chuyển trạng thái đơn hàng này?')) window.location.href='index.php?page=manage-order&status=<?= $current_status ?>&action=update_status&id=<?= $order['id'] ?>&new_status=' + this.value;">
                                            <option value="new" <?= $status == 'new' ? 'selected' : '' ?>>Mới nhận</option>
                                            <option value="confirmed" <?= $status == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                            <option value="shipping" <?= $status == 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                                            <option value="done" <?= $status == 'done' ? 'selected' : '' ?>>Hoàn thành</option>
                                            <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                        </select>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="index.php?page=order-detail&id=<?= $order['id'] ?>" class="cs-btn cs-btn-view" title="Xem chi tiết đơn">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="index.php?page=delete-order&id=<?= $order['id'] ?>" class="cs-btn cs-btn-delete" title="Xóa"
                                        onclick="event.preventDefault(); let urlXoa = this.href; openModal('Xác nhận xóa đơn hàng?', 'Bạn đang xóa đơn ORD-<?= $order['id'] ?>. Số lượng sản phẩm sẽ được trả lại vào kho. Hành động này sẽ xóa đơn hàng vĩnh viễn khỏi hệ thống!', 'fa-solid fa-trash-can', 'Xóa đơn', function() { window.location.href = urlXoa; })">
                                        <i class="fa-solid fa-trash-can"></i></a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 40px; color: #64748b;'><i class='fa-solid fa-box-open' style='font-size: 40px; color: #cbd5e1; margin-bottom: 10px; display: block;'></i>Chưa có đơn hàng nào hoặc không tìm thấy kết quả.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>