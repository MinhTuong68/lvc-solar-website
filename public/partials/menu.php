<header class="header">
        <div class="container-header">
            <div class="header-inner">
                <a href="" class="logo">
                    <div class="logo-icon">
                        <img class="logo-img" src="../webctylvc/uploads/web/logo/lvc.jpg" alt="">
                    </div>
                    <div class="logo-text">
                        <div class="brand">LVC <span>SOLAR</span></div>
                        <div class="tagline">CÔNG TY TNHH CÔNG NGHỆ - ĐẦU TƯ<br>XÂY DỰNG - BĐS - NĂNG LƯỢNG LVC</div>
                    </div>
                </a>

                <nav class="nav">
                    <a href="?page=home" class="<?= ($current_page == 'home')? 'active': ''?>">Trang chủ</a>
                    <a href="?page=about" class="<?= ($current_page == 'about')? 'active': ''?>">Giới thiệu</a>
                    <a href="?page=products" class="<?= ($current_page == 'products' || $current_page == 'detail_product' )? 'active': ''?>">Sản phẩm</a>
                    <a href="?page=projects" class="<?= ($current_page == 'projects')? 'active': ''?>">Dự án</a>
                    <a href="?page=services" class="<?= ($current_page == 'services')? 'active': ''?>">Dịch vụ</a>
                    <a href="?page=news" class="<?= ($current_page == 'news' || $current_page == 'news_detail')? 'active': ''?>">Tin tức</a>
                    <a href="?page=contact" class="<?= ($current_page == 'contact')? 'active': ''?>">Liên hệ</a>
                </nav>

                <div class="header-actions">
                    <?php 
                        // Đếm tổng số lượng sản phẩm trong Session Giỏ hàng
                        $cart_count = 0;
                        if (isset($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $quantity) {
                                $cart_count += $quantity;
                            }
                        }
                    ?>
                    <a href="?page=services#booking" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-calendar-check"></i> Đặt lịch
                    </a>
                    <a href="?page=cart" class="action-btn cart-btn">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="badge cart-badge" id="cart-badge-count"><?= $cart_count ?></span>
                    </a>
                    <a href="?page=cart" class="action-btn cart-btn" title="Lịch sử đặt hàng">
                        <i class="fa-solid fa-align-justify"></i>
                        <span class="badge cart-badge" id="cart-badge-count"><?= $cart_count ?></span>
                    </a>

                    
                </div>
            </div>   
        </div>
    </header>