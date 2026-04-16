<?php 
    session_start();
    // 1. LẤY BIẾN PAGE TỪ URL (Nếu URL không có ?page=... thì tự động gắn là 'manage-dashboard')
    $current_page = isset($_GET['page']) ? $_GET['page'] : 'manage-dashboard';
    $page_map = [
        'manage-dashboard' => 'manage-dashboard.php',
        'manage-products'  => 'manage-products.php',
        'manage-user'      => 'manage-user.php',
        'add-products'     => 'add-products.php',
        'manage-category'  => 'manage-category.php',
        'manage-brands'    => 'manage-brands.php',
        'manage-service'    => 'manage-service.php',
        'manage-contact'    => 'manage-contact.php',
        'manage-news'    => 'manage-news.php',
        'edit-product'     => 'actions/edit-product.php',
        'edit-category'     => 'actions/edit-category.php',
        'edit-product-test'     => 'actions/edit-product-test.php',
        'service_type'     => 'actions/service_type.php',
        'add-news'     => 'actions/add-news.php'
    ];
    include("partials/header.php");
    include("partials/menu.php");
 ?>
 
        <div class="main-content" id="main-wrapper">
            <header class="header">
                <i class="fa-solid fa-bars" style="font-size: 20px; cursor: pointer;" onclick="toggleSidebar()"></i>
            </header>
            <?php
                if (isset($page_map[$current_page])) {
                    include($page_map[$current_page]);
                } else {
                    echo "<h3 style='color:red;'>Lỗi 404: Trang bạn tìm không tồn tại hoặc không có quyền truy cập!</h3>";
                }
            ?>
        </div>      
<?php include("partials/footer.php") ?>