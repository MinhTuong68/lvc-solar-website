<?php
session_start();
include_once("../../config/admin/constants.php"); 
include_once("../../classes/news.php"); 

if (isset($_POST['btn_add_news'])) {
    $title   = $_POST['title'];
    $slug    = $_POST['slug'];
    $summary = $_POST['summary'];
    $content = $_POST['content'];
    $status  = $_POST['status'];
    $author  = 'Admin';
    
    // --- XỬ LÝ UPLOAD ẢNH ĐẠI DIỆN ---
    $image_name = "default-news.png"; // Ảnh mặc định nếu không chọn ảnh
    
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        
        $file_name = $_FILES['image']['name'];
       
        $image_name = time() . '_' . $file_name; 
        
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
        header("Location: ../index.php?page=service_type");
    }
    exit();
} else {
    header("location: ../index.php?page=manage-news");
    exit();
}
?>