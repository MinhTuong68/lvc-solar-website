<?php
include_once("../../config/constants.php");
include_once("../../classes/news.php");
include_once('../admin/auth.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $news_obj = new News($conn);
    
    // Gọi hàm xóa
    $is_deleted = $news_obj->deleteNews($id);
    
    if ($is_deleted) {
        $_SESSION['toast_message'] = "Đã xóa bài viết thành công!";
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_message'] = "Lỗi: Không thể xóa bài viết này!";
        $_SESSION['toast_type'] = 'error';
    }
}

// Xóa xong tự động quay lại trang danh sách
header("Location: ../index.php?page=manage-news");
exit();
?>