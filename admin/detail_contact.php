<?php
    include("../classes/contact.php");
    $contact_obj = new Contact($conn);

    if (!isset($_GET['id'])) {
        header("Location: index.php?page=manage-contact");
        exit;
    }

    $id = (int)$_GET['id'];

    // Lấy thông tin chi tiết của Contact
    $sql = "SELECT * FROM tbl_contacts WHERE id = $id";
    $res = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($res) == 0) {
        $_SESSION['toast_message'] = "Không tìm thấy yêu cầu liên hệ này!";
        $_SESSION['toast_type'] = "error";
        header("Location: index.php?page=manage-contact");
        exit;
    }

    $contact = mysqli_fetch_assoc($res);

    // Cập nhật trạng thái thành "Đã đọc" (nếu chưa đọc)
    if ($contact['status'] == 0) {
        mysqli_query($conn, "UPDATE tbl_contacts SET status = 1 WHERE id = $id");
        $contact['status'] = 1; // Cập nhật lại biến hiển thị
    }

    // Format ngày giờ
    $formatted_date = date('d/m/Y - H:i', strtotime($contact['created_at']));
    $status_badge = ($contact['status'] == 1) ? "<span class='cs-badge badge-done'><i class='fa-solid fa-check'></i> Đã đọc & Liên hệ</span>" : "<span class='cs-badge badge-new'>Mới nhận</span>";
?>

<!-- Gọi file CSS vừa tách vào đây -->
<link rel="stylesheet" href="assets/css/detail-contact.css">

<div class="wrapper">
    <div class="cs-page-wrapper">
        <div class="lvc-dc-header">
            <div>
                <h1 class="lvc-dc-title"><i class="fa-solid fa-envelope-open-text" style="color: #3b82f6;"></i> Chi tiết tin nhắn liên hệ</h1>
                <span class="lvc-dc-meta">Mã số: <strong>#<?= $contact['id'] ?></strong> | Ngày gửi: <?= $formatted_date ?></span>
            </div>
            <div>
                <a href="index.php?page=manage-contact" class="lvc-dc-btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="lvc-dc-grid">
            
            <!-- Cột Trái: Thông tin khách hàng -->
            <div class="lvc-dc-card lvc-dc-card-left">
                <div class="lvc-dc-card-head">
                    <i class="fa-solid fa-user-circle"></i> Thông tin người gửi
                </div>
                <div class="lvc-dc-card-body">
                    <div class="lvc-dc-info-item">
                        <span class="lvc-dc-info-label">Họ và tên</span>
                        <div class="lvc-dc-info-value"><?= htmlspecialchars($contact['fullname']) ?></div>
                    </div>
                    
                    <div class="lvc-dc-info-item">
                        <span class="lvc-dc-info-label">Số điện thoại</span>
                        <div class="lvc-dc-info-value lvc-dc-highlight">
                            <a href="tel:<?= htmlspecialchars($contact['phone']) ?>">
                                <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($contact['phone']) ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="lvc-dc-info-item">
                        <span class="lvc-dc-info-label">Email</span>
                        <div class="lvc-dc-info-value lvc-dc-normal">
                            <?php if(!empty($contact['email'])) { ?>
                                <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                                    <i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($contact['email']) ?>
                                </a>
                            <?php } else { ?>
                                <span style="font-style: italic; color: #cbd5e1;">Không cung cấp</span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="lvc-dc-status-box">
                        <span class="lvc-dc-info-label" style="margin-bottom: 10px;">Trạng thái xử lý</span>
                        <?= $status_badge ?>
                    </div>
                </div>
            </div>

            <!-- Cột Phải: Nội dung tin nhắn & Hành động -->
            <div class="lvc-dc-card lvc-dc-card-right">
                <div class="lvc-dc-msg-head">
                    <i class="fa-solid fa-comment-dots"></i> Nội dung liên hệ
                </div>
                
                <div class="lvc-dc-msg-body">
                    <div class="lvc-dc-msg-content">
                        <?= htmlspecialchars($contact['message']) ?>
                    </div>
                </div>

                <div class="lvc-dc-actions">
                    <a href="tel:<?= htmlspecialchars($contact['phone']) ?>" class="lvc-dc-btn lvc-dc-btn-call">
                        <i class="fa-solid fa-phone-volume"></i> Gọi khách hàng
                    </a>

                    <a href="actions/delete-contact.php?id=<?= $contact['id'] ?>" class="lvc-dc-btn lvc-dc-btn-delete"
                        onclick="event.preventDefault(); let urlXoa = this.href; openModal('Xác nhận xóa?', 'Bạn có chắc chắn muốn xóa tin nhắn của khách hàng <?= htmlspecialchars($contact['fullname']) ?> không?', 'fa-solid fa-trash-can', 'Xóa tin nhắn', function() { window.location.href = urlXoa; })">
                        <i class="fa-solid fa-trash-can"></i> Xóa tin nhắn
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>