<?php
    // Sửa lại đường dẫn kết nối Database cho đúng với dự án của bạn
    include('../../config/constants.php'); 
    include_once('../admin/auth.php');

    if (isset($_POST['slug'])) {
        $slug = trim($_POST['slug']);
        
        // Kiểm tra xem slug đã có trong bảng tbl_products chưa
        $sql = "SELECT id FROM tbl_products WHERE slug = '$slug' LIMIT 1";
        $res = $conn->query($sql);
        
        if ($res && $res->num_rows > 0) {
            echo json_encode(['exists' => true]); // Đã tồn tại
        } else {
            echo json_encode(['exists' => false]); // An toàn
        }
    }
?>