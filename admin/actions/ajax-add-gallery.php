<?php
    include('../../config/constants.php');
    include('../../classes/products.php');
    include_once('../admin/auth.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id']) && isset($_FILES['new_gallery'])) {
        $product_id = (int)$_POST['product_id'];
        $productManager = new Product($conn);
        $uploaded_images = [];
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif']; // Whitelist

        $total_files = count($_FILES['new_gallery']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['new_gallery']['name'][$i] != "") {
                
                $ext = strtolower(pathinfo($_FILES['new_gallery']['name'][$i], PATHINFO_EXTENSION));
                
                // Kiểm tra đuôi file an toàn
                if (in_array($ext, $allowed_ext)) {
                    $tmp_name = $_FILES['new_gallery']['tmp_name'][$i];
                    $new_name = "gallery_" . time() . "_" . rand(100,999) . "." . $ext;
                    $upload_path = "../../uploads/products/image_gallery/" . $new_name;

                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        $productManager->addGalleryImage($product_id, $new_name);
                        
                        $new_id = mysqli_insert_id($conn);
                        $uploaded_images[] = [
                            'id' => $new_id,
                            'image_path' => $new_name
                        ];
                    }
                }
            }
        }
        
        // Trả về JSON để Frontend JS xử lý
        echo json_encode(['status' => 'success', 'images' => $uploaded_images]);
    }
?>