<?php
    session_start();
    include('../../config/admin/constants.php');
    include('../../classes/products.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id']) && isset($_FILES['new_gallery'])) {
        $product_id = (int)$_POST['product_id'];
        $productManager = new Product($conn);
        $uploaded_images = [];

        $total_files = count($_FILES['new_gallery']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['new_gallery']['name'][$i] != "") {
                $tmp_name = $_FILES['new_gallery']['tmp_name'][$i];
                $new_name = "gallery_" . time() . "_" . rand(100,999) . "_" . $_FILES['new_gallery']['name'][$i];
                $upload_path = "../../uploads/products/image_gallery/" . $new_name;

                if (move_uploaded_file($tmp_name, $upload_path)) {
                    $productManager->addGalleryImage($product_id, $new_name);
                    
                    // Lấy ID vừa được tạo trong CSDL để trả về cho Frontend
                    $new_id = mysqli_insert_id($conn);
                    $uploaded_images[] = [
                        'id' => $new_id,
                        'image_path' => $new_name
                    ];
                }
            }
        }
        echo json_encode(['status' => 'success', 'images' => $uploaded_images]);
    }
?>