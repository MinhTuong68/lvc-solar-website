<?php
    // Đường dẫn lùi 2 cấp (từ admin/actions/ ra ngoài thư mục gốc)
        include('../../config/constants.php'); 
        include('../../classes/projects.php'); 
        include_once('../admin/auth.php');

    // XỬ LÝ XÓA DỰ ÁN
    if (isset($_GET['type']) && $_GET['type'] == 'project' && isset($_GET['id'])) {
        
        
        $id = (int)$_GET['id'];
        $projectObj = new Project($conn);
        
        if ($projectObj->deleteProject($id)) {
            $_SESSION['toast_message'] = "Xóa dự án thành công!";
            $_SESSION['toast_type'] = "success";
        } else {
            $_SESSION['toast_message'] = "Lỗi: Không thể xóa dự án!";
            $_SESSION['toast_type'] = "error";
        }
        
        // Quay về trang quản lý dự án
        header("Location: ../index.php?page=manage-projects");
        exit;
    }
?>