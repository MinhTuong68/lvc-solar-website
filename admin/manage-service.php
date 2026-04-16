<?php
    include("../classes/services.php");
    $service_obj = new Service($conn);

    // 2. LẤY CÁC BIẾN TỪ URL (Lọc dữ liệu)
    $current_status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $current_type = isset($_GET['service_type']) ? $_GET['service_type'] : '';
    $current_search = isset($_GET['search']) ? $_GET['search'] : '';

    // 3. LẤY DỮ LIỆU TỪ DATABASE
    $allServices = $service_obj->getAllServices($current_status, $current_type, $current_search);
    $allTypes = $service_obj->getAllServiceTypes(); // Để đổ vào Dropdown
?>
<div class="wrapper">
    <div class="cs-page-wrapper">
        <div class="cs-header">
            <h1>Quản lý yêu cầu báo giá & dịch vụ</h1>
            <span style="color: #64748b; font-size: 14px;">Kinh doanh / </span>
            <span style="font-size: 14px; font-weight: bold;">Dịch vụ Solar</span>
        </div>

        <div class="cs-tabs">
            <a href="index.php?page=manage-service&status=all" class="cs-tab <?php echo ($current_status == 'all') ? 'active' : ''; ?>">Tất cả</a>
            <a href="index.php?page=manage-service&status=new" class="cs-tab <?php echo ($current_status == 'new') ? 'active' : ''; ?>">Mới</a>
            <a href="index.php?page=manage-service&status=called" class="cs-tab <?php echo ($current_status == 'called') ? 'active' : ''; ?>">Đã gọi</a>
            <a href="index.php?page=manage-service&status=surveying" class="cs-tab <?php echo ($current_status == 'surveying') ? 'active' : ''; ?>">Khảo sát</a>
            <a href="index.php?page=manage-service&status=quoted" class="cs-tab <?php echo ($current_status == 'quoted') ? 'active' : ''; ?>">Đã báo giá</a>
            <a href="index.php?page=manage-service&status=done" class="cs-tab <?php echo ($current_status == 'done') ? 'active' : ''; ?>">Hoàn thành</a>
            <a href="index.php?page=manage-service&status=cancelled" class="cs-tab <?php echo ($current_status == 'cancelled') ? 'active' : ''; ?>">Huỷ</a>
        </div>

        <div class="cs-filter-bar">
            <form action="" method="GET" class = "filter-bar">
                <input type="hidden" name="page" value="manage-service">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($current_status); ?>">
                <a href="index.php?page=service_type" class="cs-btn-addservices"><i class="fa-solid fa-plus"></i> Thêm dịch vụ mới</a>
                <input type="text" name="search" class="cs-select" placeholder="🔍 Tìm khách hàng, SĐT..." value="<?php echo htmlspecialchars($current_search); ?>">
                <select name="service_type" class="cs-select">
                    <option value="">Tất cả dịch vụ</option>
                    <?php 
                        if(!empty($allTypes)){
                            foreach($allTypes as $type){
                                // Nếu ID đang được lọc thì in chữ 'selected' để giữ nguyên tùy chọn
                                $selected = ($current_type == $type['id']) ? 'selected' : '';
                                echo '<option value="'.$type['id'].'" '.$selected.'>' . htmlspecialchars($type['service_type']) . '</option>';
                            }
                        }
                    ?>
                </select>
                <button type="submit" class="cs-btn-filter"><i class="fa-solid fa-filter"></i> Lọc</button>
                <a href="index.php?page=manage-service" class="cs-btn-clear">Xóa</a>
            </form>
        </div>

        <div class="cs-table-container">
            <table class="cs-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Loại Dịch vụ</th>
                            <th>Tiền điện / Tháng</th>
                            <th>Ngày hẹn khảo sát</th>
                            <th>Trạng thái</th>
                            <th style="text-align: right;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            // SỬ DỤNG MẢNG ĐÃ ĐƯỢC LẤY TỪ CLASS OOP
                            if(!empty($allServices)) {
                                $stt=1;
                                foreach($allServices as $row) {
                                    $id = $row['id'];
                                    $customer_name = $row['customer_name'];
                                    $phone = $row['phone'];
                                    // Lấy chữ trực tiếp từ DB do đã dùng lệnh JOIN
                                    $service_label = isset($row['service_type']) ? $row['service_type'] : '<span style="color:red">Lỗi DM</span>';
                                    $electricity_bill = $row['electricity_bill'];
                                    $booking_date = $row['booking_date'];
                                    $status = $row['status'];

                                    // Xử lý UI Trạng thái
                                    $status_badge = "";
                                    if($status == 'new') $status_badge = "<span class='cs-badge badge-new'>Mới nhận</span>";
                                    elseif($status == 'called') $status_badge = "<span class='cs-badge badge-called'>Đã liên hệ</span>";
                                    elseif($status == 'surveying') $status_badge = "<span class='cs-badge badge-surveying'>Đang khảo sát</span>";
                                    elseif($status == 'quoted') $status_badge = "<span class='cs-badge badge-quoted'>Đã báo giá</span>";
                                    elseif($status == 'done') $status_badge = "<span class='cs-badge badge-done'>Hoàn thành</span>";
                                    elseif($status == 'cancelled') $status_badge = "<span class='cs-badge badge-cancelled'>Đã hủy</span>";
                                    
                                    // Format ngày hẹn
                                    $formatted_date = ($booking_date && $booking_date != '0000-00-00') ? date('d/m/Y', strtotime($booking_date)) : '<span style="color:#94a3b8">Chưa xác định</span>';
                                    ?>
                                    <tr>
                                        <td>#<?php echo $stt++; ?></td>
                                        <td>
                                            <span class="cs-customer-name"><?php echo htmlspecialchars($customer_name); ?></span>
                                            <span class="cs-customer-sub"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($phone); ?></span>
                                        </td>
                                        <td style="font-weight: 500; color: #3b82f6;"><?php echo htmlspecialchars($service_label); ?></td>
                                        <td style="color: #ef4444; font-weight: 500;"><?php echo htmlspecialchars($electricity_bill); ?></td>
                                        <td><?php echo $formatted_date; ?></td>
                                        <td><?php echo $status_badge; ?></td>
                                        <td style="text-align: right;">
                                            <a href="#" class="cs-btn cs-btn-view" title="Xem & Cập nhật"><i class="fa-solid fa-eye"></i></a>
                                            <a href="#" class="cs-btn cs-btn-delete" title="Xóa"><i class="fa-solid fa-trash-can"></i></a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='7' style='text-align:center; padding: 30px; color: #64748b;'>Chưa có yêu cầu dịch vụ nào hoặc không tìm thấy dữ liệu lọc.</td></tr>";
                            }
                        ?>
                    </tbody>
            </table>
        </div>
    </div>
</div>