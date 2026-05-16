<?php
include_once("../../config/constants.php"); 
include_once("../../classes/news.php"); 
include('../auth.php'); 
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die(json_encode(['uploaded' => 0, 'error' => ['message' => 'Unauthorized']]));
}
if (isset($_POST['btn_add_news'])) {
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['toast_message'] = "Yêu cầu không hợp lệ!";
        $_SESSION['toast_type'] = 'error';
        header("Location: ../index.php?page=manage-add-news");
        exit();
    }

    $title   = e($_POST['title'] ?? '');
    $slug    = e($_POST['slug'] ?? '');
    $summary = e($_POST['summary'] ?? '');
    $status  = (int)($_POST['status'] ?? 0);
    $author  = 'Admin';
    $content = $_POST['content'] ?? '';
    
    // --- XỬ LÝ UPLOAD ẢNH ĐẠI DIỆN ---
    $image_name = "default-news.png"; // Ảnh mặc định nếu không chọn ảnh
    
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        
        $file_name = $_FILES['image']['name'];
       
        $image_name = time() . '_' . $file_name; 
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed)) {
            die("Lỗi: Chỉ cho phép ảnh jpg, png, webp, gif!");
        }
        
        $source_path = $_FILES['image']['tmp_name'];
       
        $destination_path = "../../uploads/news/images/" . $image_name; 
        
        
        $upload = move_uploaded_file($source_path, $destination_path);
        if (!$upload) {
            die("Lỗi: Không thể tải ảnh lên hệ thống.");
        }
    }
    
   
    $news_obj = new News($conn);
    
    $is_inserted = $news_obj->addNews($title, $slug, $image_name, $summary, $content, $status, $author);
    
    if ($is_inserted) {
        $_SESSION['toast_message'] = "Thêm thành công!";
        $_SESSION['toast_type'] = 'success';  
        header("Location: ../index.php?page=manage-add-news"); 
    } else {
        $_SESSION['toast_message'] = "Thêm thất bại, vui lòng thử lại!";
        $_SESSION['toast_type'] = 'error';  
        header("Location: ../index.php?page=manage-news");
    }
    exit();
} else {
    header("location: ../index.php?page=manage-news");
    exit();
}
?>