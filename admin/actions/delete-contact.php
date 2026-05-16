<?php
    define('IS_SECURE', true);
    include('../../config/constants.php');
    include('../../classes/contact.php');
    include_once('../admin/auth.php');

    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $contact_obj = new Contact($conn);
        
        if ($contact_obj->deleteContact($id)) {
            $_SESSION['toast_message'] = "Đã xóa tin nhắn liên hệ thành công!";
            $_SESSION['toast_type'] = "success";
        } else {
            $_SESSION['toast_message'] = "Lỗi: Không thể xóa tin nhắn này!";
            $_SESSION['toast_type'] = "error";
        }
    }

    // Xóa xong tự động quay lại trang danh sách liên hệ
    header("Location: ../index.php?page=manage-contact");
    exit();
?>