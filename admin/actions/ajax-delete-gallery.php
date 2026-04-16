<?php
    session_start();
    include('../../config/admin/constants.php');
    include('../../classes/products.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gal_id'])) {
        $gal_id = (int)$_POST['gal_id'];
        $productManager = new Product($conn);
        $productManager->deleteGalleryImage($gal_id);
        
        echo json_encode(['status' => 'success']);
    }
?>