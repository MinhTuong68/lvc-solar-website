<?php
    define('IS_SECURE', true);
    include('../config/constants.php');
?>
<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
        <title>Điện năng lượng mặt trời miền tây</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/phone.css">
        <link rel="stylesheet" href="assets/css/chatbot.css">
        <script src="assets/js/publics.js"></script>
        <script>
            const ROOT_URL = '<?php echo ROOT_URL; ?>';
        </script>
        <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        />
    </head>
    <body>
        <?php
            require_once '../classes/setting.php';
            $settingObj = new Setting($conn);
            $settings = $settingObj->getSettings();

            $site_name = $settings['site_name'] ?? 'LVC SOLAR';
            $site_desc = $settings['site_description'] ?? 'CÔNG TY TNHH CÔNG NGHỆ - ĐẦU TƯ XÂY DỰNG - BĐS - NĂNG LƯỢNG LVC';
            $logo_file = $settings['logo'] ?? 'lvc.jpg';
        ?>
        <?php
            $current_page = isset($_GET['page']) ? $_GET['page'] : 'home';
            $page_map = [
                'home' => 'home.php',
                'products'  => 'products.php',
                'product-test'  => 'product-test.php',
                'detail_product'      => 'detail_product.php',
                'add-products'     => 'add-products.php',
                'services'  => 'services.php',
                'about'  => 'about.php',
                'about-test'  => 'about-test.php',
                'contact' => 'contact.php',
                'manage-brands'    => 'manage-brands.php',
                'cart'     => 'cart.php',
                'news'     => 'news.php',
                'news_detail'     => 'news_detail.php',
                'checkout'     => 'checkout.php',
                'projects'     => 'projects.php',
                'project-test'     => 'project-test.php',
                'detail_project'     => 'detail_project.php',
                'order_history'     => 'order_history.php',
                'order-history-test'     => 'order-history-test.php',
                'edit_order'     => 'edit_order.php',
                'detail_order'     => 'detail_order.php',
                'print-bill'     => 'print-bill.php',
                'chatbot'     => 'chatbot.php',
                'chatbot_api'     => 'chatbot_api.php',
                'add-cart'      => 'actions/add-cart.php',
                'edit-product'     => 'actions/edit-product.php',
                'add-service'     => 'actions/add-service.php',
                'process-edit-order'     => 'actions/process-edit-order.php',
                'edit-category'     => 'actions/edit-category.php',
                'update-clientorder'     => 'actions/update-clientorder.php',
                'edit-product-test'     => 'actions/edit-product-test.php',
                'cancel-order'     => 'actions/cancel-order.php',
                'report-review'     => 'actions/report-review.php'
            ];
        ?>
    <div id="toast-container" class="toast-container"></div>
    <div class="topbar">
        <div class="container">
            <div class="topbar-inner">
                <div class="topbar-left">
                    <a href="">
                        <i class="fa-solid fa-phone"></i>
                        Hotline: <span class="hotline">0945671536</span>
                    </a>

                    <a href="">
                        <i class="fa-solid fa-envelope"></i> <?php echo $settings['email'] ?>
                    </a>
                </div>

                <div class="topbar-right">
                    <a href="<?php echo $settings['facebook_link'] ?>">
                        <i class="fa-brands fa-facebook-f"></i> Facebook
                    </a>
                    <a href="<?php echo $settings['youtube_link'] ?>">
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
                    showToast("<?= e($_SESSION['toast_message']) ?>", "<?= e($type) ?>");
                });
            </script>
            
            <?php 
                unset($_SESSION['toast_message']); 
                unset($_SESSION['toast_type']); 
            ?>
        <?php } 
    ?>

    
    