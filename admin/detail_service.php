<?php
    include_once("../classes/services.php");
    $service_obj = new Service($conn);

    if (!isset($_GET['id'])) {
        header("Location: index.php?page=manage-service");
        exit;
    }

    $id = (int)$_GET['id'];
    if (isset($_POST['btn_update_status'])) {
        $new_status = $_POST['new_status'];
        if ($service_obj->updateServiceStatus($id, $new_status)) {
            $_SESSION['toast_message'] = "Cập nhật trạng thái thành công!";
            $_SESSION['toast_type'] = "success";
        } else {
            $_SESSION['toast_message'] = "Lỗi: Không thể cập nhật trạng thái!";
            $_SESSION['toast_type'] = "error";
        }
        // F5 lại trang để hiển thị màu huy hiệu mới
        header("Location: index.php?page=detail_service&id=" . $id);
        exit;
    }
    $srv = $service_obj->getServiceById($id);

    if (!$srv) {
        $_SESSION['toast_message'] = "Yêu cầu dịch vụ không tồn tại!";
        $_SESSION['toast_type'] = "error";
        header("Location: index.php?page=manage-service");
        exit;
    }

    // Xử lý hiển thị huy hiệu trạng thái
    // Xử lý hiển thị huy hiệu trạng thái (Đồng bộ 6 bước)
    $status_html = '';
    if ($srv['status'] == 'new') {
        $status_html = '<span class="lvc-badge lvc-badge-new"><i class="fa-solid fa-bell"></i> Mới nhận</span>';
    } elseif ($srv['status'] == 'called') {
        $status_html = '<span class="lvc-badge" style="background:#fef3c7;color:#b45309;"><i class="fa-solid fa-phone"></i> Đã liên hệ</span>';
    } elseif ($srv['status'] == 'surveying') {
        $status_html = '<span class="lvc-badge" style="background:#e0e7ff;color:#4338ca;"><i class="fa-solid fa-location-crosshairs"></i> Đang khảo sát</span>';
    } elseif ($srv['status'] == 'quoted') {
        $status_html = '<span class="lvc-badge" style="background:#fce7f3;color:#be185d;"><i class="fa-solid fa-file-invoice-dollar"></i> Đã báo giá</span>';
    } elseif ($srv['status'] == 'done') {
        $status_html = '<span class="lvc-badge lvc-badge-completed"><i class="fa-solid fa-check-double"></i> Hoàn thành</span>';
    } else {
        $status_html = '<span class="lvc-badge lvc-badge-cancelled"><i class="fa-solid fa-ban"></i> Đã hủy</span>';
    }
?>

<div class="wrapper lvc-srv-detail-wrapper">
    <div class="container-fluid">
        <div class="lvc-srv-header">
            <div>
                <h2 class="lvc-srv-title">Chi Tiết Yêu Cầu #<?= $srv['id'] ?></h2>
                <span style="color: #64748b; font-size: 0.9rem;">Ngày gửi: <?= date('d/m/Y H:i', strtotime($srv['created_at'])) ?></span>
            </div>
            <div>
                <?= $status_html ?>
            </div>
        </div>

        <div class="lvc-srv-grid">
            <div class="lvc-srv-card">
                <div class="lvc-srv-card-title">
                    <i class="fa-solid fa-user-check"></i> Thông Tin Khách Hàng
                </div>
                
                <div class="lvc-srv-info-row">
                    <div class="lvc-srv-info-label">Họ và tên:</div>
                    <div class="lvc-srv-info-value"><?= htmlspecialchars($srv['customer_name']) ?></div>
                </div>
                <div class="lvc-srv-info-row">
                    <div class="lvc-srv-info-label">Số điện thoại:</div>
                    <div class="lvc-srv-info-value">
                        <a href="tel:<?= htmlspecialchars($srv['phone']) ?>" style="color: #3b82f6; font-weight: 600; text-decoration: none;">
                            <?= htmlspecialchars($srv['phone']) ?>
                        </a>
                    </div>
                </div>
                <div class="lvc-srv-info-row">
                    <div class="lvc-srv-info-label">Email:</div>
                    <div class="lvc-srv-info-value"><?= !empty($srv['email']) ? htmlspecialchars($srv['email']) : '<i style="color:#94a3b8;">Không cung cấp</i>' ?></div>
                </div>
                <div class="lvc-srv-info-row">
                    <div class="lvc-srv-info-label">Địa chỉ khảo sát:</div>
                    <div class="lvc-srv-info-value"><?= !empty($srv['address']) ? htmlspecialchars($srv['address']) : '<i style="color:#94a3b8;">Chưa cập nhật chi tiết</i>' ?></div>
                </div>
            </div>

            <div class="lvc-srv-card">
                <div class="lvc-srv-card-title" style="color: #f59e0b;">
                    <i class="fa-solid fa-bolt"></i> Dữ Liệu Kỹ Thuật & Dịch Vụ
                </div>
                
                <div class="lvc-srv-info-row">
                    <div class="lvc-srv-info-label">Loại dịch vụ:</div>
                    <div class="lvc-srv-info-value" style="color: #0284c7; font-weight: 700;">
                        <?= htmlspecialchars($srv['service_type']) ?>
                    </div>
                </div>
                <div class="lvc-srv-info-row">
                    <div class="lvc-srv-info-label">Hóa đơn điện:</div>
                    <div class="lvc-srv-info-value highlight">
                        <?= !empty($srv['electricity_bill']) ? htmlspecialchars($srv['electricity_bill']) : '--' ?>
                    </div>
                </div>
                <div class="lvc-srv-info-row">
                    <div class="lvc-srv-info-label">Công suất dự kiến:</div>
                    <div class="lvc-srv-info-value">
                        <?= !empty($srv['capacity']) ? htmlspecialchars($srv['capacity']) : 'Khách chưa rõ' ?>
                    </div>
                </div>
                <div class="lvc-srv-info-row">
                    <div class="lvc-srv-info-label">Lịch hẹn khảo sát:</div>
                    <div class="lvc-srv-info-value">
                        <?php 
                            if(!empty($srv['booking_date'])) {
                                echo '<span style="color:#00875a; font-weight:bold;"><i class="fa-regular fa-calendar-check"></i> ' . date('d/m/Y', strtotime($srv['booking_date'])) . '</span>';
                            } else {
                                echo '<i style="color:#94a3b8;">Cần gọi để chốt lịch hẹn</i>';
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="lvc-srv-issue-box">
            <h4><i class="fa-solid fa-comment-dots" style="color: #64748b; margin-right: 8px;"></i> Nội dung yêu cầu / Ghi chú của khách hàng:</h4>
            <p><?= !empty($srv['issue_description']) ? nl2br(htmlspecialchars($srv['issue_description'])) : 'Khách hàng không để lại ghi chú thêm.' ?></p>
        </div>

        <div class="lvc-srv-actions">
            <a href="index.php?page=manage-service" class="lvc-btn lvc-btn-back">
                <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
            </a>
            
            <a href="tel:<?= htmlspecialchars($srv['phone']) ?>" class="lvc-btn lvc-btn-call">
                <i class="fa-solid fa-phone-volume"></i> Gọi khách hàng ngay
            </a>
            
        </div>
    </div>
</div>