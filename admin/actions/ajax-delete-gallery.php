<?php
    include('../../config/constants.php');
    include('../../classes/products.php');
    include_once('../admin/auth.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gal_id'])) {
        $gal_id = (int)$_POST['gal_id'];
        
        // 1. Tìm tên file ảnh trong CSDL trước khi xóa
        $stmt = $conn->prepare("SELECT image_path FROM tbl_product_gallery WHERE id = ?");
        $stmt->bind_param("i", $gal_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            $image_path = "../../uploads/products/image_gallery/" . $row['image_path'];
            
            // 2. Xóa file cứng trên máy chủ (Nếu tồn tại)
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // 3. Xóa record trong Database
        $productManager = new Product($conn);
        $productManager->deleteGalleryImage($gal_id);
        
        echo json_encode(['status' => 'success']);
    }
?>