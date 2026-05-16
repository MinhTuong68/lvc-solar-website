<?php
    include('../../config/constants.php');
    include_once('../admin/auth.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sort_order'])) {
        $sort_order = $_POST['sort_order']; // Đây là mảng chứa danh sách ID ảnh theo thứ tự mới
        $order = 1; // Khởi tạo số thứ tự bắt đầu từ 1

        foreach ($sort_order as $gal_id) {
            $gal_id = (int)$gal_id;
            // Cập nhật lại số thứ tự cho từng ảnh trong CSDL
            mysqli_query($conn, "UPDATE tbl_product_gallery SET sort_order = $order WHERE id = $gal_id");
            $order++; // Tăng dần 1, 2, 3, 4...
        }

        echo json_encode(['status' => 'success', 'message' => 'Đã lưu vị trí ảnh!']);
    }
?>