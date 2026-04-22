<?php
    define('IS_SECURE', true);
    include('../config/publics/constants.php');
?>
<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin LVC</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <script src="assets/js/publics.js"></script>
        <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        />
    </head>
    <body>
        <?php
            $current_page = isset($_GET['page']) ? $_GET['page'] : 'home';
            $page_map = [
                'home' => 'home.php',
                'products'  => 'products.php',
                'detail_product'      => 'detail_product.php',
                'add-products'     => 'add-products.php',
                'services'  => 'services.php',
                'about'  => 'about.php',
                'contact' => 'contact.php',
                'manage-brands'    => 'manage-brands.php',
                'cart'     => 'cart.php',
                'news'     => 'news.php',
                'news_detail'     => 'news_detail.php',
                'checkout'     => 'checkout.php',
                'projects'     => 'projects.php',
                'edit-product'     => 'actions/edit-product.php',
                'edit-category'     => 'actions/edit-category.php',
                'edit-product-test'     => 'actions/edit-product-test.php'
            ];
        ?>
    <div id="toast-container" class="toast-container"></div>
    <div class="topbar">
        <div class="container">
            <div class="topbar-inner">
                <div class="topbar-left">
                    <a href="">
                        <i class="fa-solid fa-phone"></i>
                        Hotline: <span class="hotline">0865469950</span>
                    </a>

                    <a href="">
                        <i class="fa-solid fa-envelope"></i> lvc@gmail.com
                    </a>
                </div>

                <div class="topbar-right">
                    <a href="">
                        <i class="fa-brands fa-facebook-f"></i> Facebook
                    </a>
                    <a href="">
                        <i class="fa-brands fa-youtube"></i> Youtube
                    </a>
                    <span>Khảo sát miễn phí toàn các tỉnh miền tây</span>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['toast_message'])) { 
            $type = isset($_SESSION['toast_type']) ? $_SESSION['toast_type'] : 'success';
        ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast("<?= $_SESSION['toast_message'] ?>", "<?= $type ?>");
                });
            </script>
            
            <?php 
                unset($_SESSION['toast_message']); 
                unset($_SESSION['toast_type']); 
            ?>
        <?php } 
    ?>

    
    