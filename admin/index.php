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
        'manage-add-news'    => 'manage-add-news.php',
        'manage-order'    => 'manage-order.php',
        'manage-projects'    => 'manage-projects.php',
        'manage-add-projects'    => 'manage-add-projects.php',
        'edit-product'     => 'edit-product.php',
        'edit-category'     => 'edit-category.php',
        'edit-news'     => 'edit-news.php',
        'edit-project'     => 'edit-project.php',
        'service_type'     => 'service_type.php',
        'order-detail'     => 'order-detail.php',
        'detail_service'     => 'detail_service.php',
        'print-bill'     => 'print-bill.php',
        'login-admin'     => 'login-admin.php',
        'auth'     => 'auth.php',
        'detail_contact'     => 'detail_contact.php',
        'edit-brand'     => 'edit-brand.php',
        'manage-reviews'     => 'manage-reviews.php',
        'manage-admin'     => 'manage-admin.php',
        'manage-setting'     => 'manage-setting.php',
        'update-setting'     => 'actions/update-setting.php',
        'add-news'     => 'actions/add-news.php',
        'delete-contact'     => 'actions/delete-contact.php',
        'delete-order'     => 'actions/delete-order.php',
        'delete-project'     => 'actions/delete-project.php'
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