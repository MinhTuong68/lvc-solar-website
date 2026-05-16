<header class="header">
        <div class="container-header">
            <div class="header-inner">
                <div class="mobile-menu-btn">
                    <i class="fa-solid fa-bars"></i>
                </div>

                <a href="?page=home" class="logo">
                    <div class="logo-icon">
                        <img class="logo-img" src="<?php echo ROOT_URL ?>uploads/web/logo/<?php echo $logo_file; ?>" alt="">
                    </div>
                    <div class="logo-text">
                        <div class="brand">LVC <span>SOLAR</span></div>
                        <div class="tagline"><?php echo nl2br($site_desc); ?></div>
                    </div>
                </a>

                <nav class="nav nav-menu">
                    <a href="?page=home" class="<?= ($current_page == 'home')? 'active': ''?>">Trang chủ</a>
                    <a href="?page=about" class="<?= ($current_page == 'about')? 'active': ''?>">Giới thiệu</a>
                    <a href="?page=products" class="<?= ($current_page == 'products' || $current_page == 'detail_product' )? 'active': ''?>">Sản phẩm</a>
                    <a href="?page=projects" class="<?= ($current_page == 'projects' || $current_page == 'detail_project' )? 'active': ''?>">Dự án</a>
                    <a href="?page=services" class="<?= ($current_page == 'services')? 'active': ''?>">Dịch vụ</a>
                    <a href="?page=news" class="<?= ($current_page == 'news' || $current_page == 'news_detail')? 'active': ''?>">Tin tức</a>
                    <a href="?page=contact" class="<?= ($current_page == 'contact')? 'active': ''?>">Liên hệ</a>
                    <a href="?page=order_history" class="<?= ($current_page == 'order_history')? 'active': ''?> order-history">Lịch sử mua hàng</a>
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

                    <div class="cart-dropdown-wrapper">  
                        <a href="?page=cart" class="action-btn cart-btn" id="cart-toggle-btn">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <?php 
                                // Lấy số lượng mới thêm, nếu không có thì bằng 0
                                $new_count = isset($_SESSION['new_added_count']) ? $_SESSION['new_added_count'] : 0;
                                
                                if ($new_count > 0 && $current_page != 'cart') {
                                    echo '<span class="badge cart-badge" id="cart-badge-count">' . $new_count . '</span>';
                                } else {
                                    // Phải có dòng ẩn này để Javascript gọi lên không bị méo giao diện
                                    echo '<span class="badge cart-badge" id="cart-badge-count" style="display: none;"></span>';
                                }
                            ?>
                        </a>

                        <div class="cart-dropdown-panel" id="cart-dropdown-panel">
                            <div class="cdp-header">
                                <span><i class="fa-solid fa-cart-shopping"></i> Giỏ hàng</span>
                                <a href="?page=cart" class="cdp-view-all">Xem đầy đủ →</a>
                            </div>

                            <!-- DANH SÁCH SẢN PHẨM TRONG GIỎ -->
                            <div class="cdp-items">
                                <?php
                                if (!empty($_SESSION['cart']) && !empty($_SESSION['cart_details'])):
                                    $cart_total = 0;
                                    foreach ($_SESSION['cart_details'] as $item):
                                        $subtotal = $item['price'] * $item['quantity'];
                                        $cart_total += $subtotal;
                                ?>
                                <div class="cdp-item">
                                    <img src="<?= ROOT_URL ?>uploads/products/images/<?= htmlspecialchars($item['image']) ?>" 
                                        onerror="this.src='<?= ROOT_URL ?>uploads/web/default.jpg'" 
                                        alt="<?= htmlspecialchars($item['name']) ?>">
                                    <div class="cdp-item-info">
                                        <div class="cdp-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                        <div class="cdp-item-qty-price">
                                            x<?= $item['quantity'] ?> &nbsp;·&nbsp;
                                            <span class="cdp-item-price"><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div class="cdp-total">
                                    <span>Tổng cộng:</span>
                                    <strong><?= number_format($cart_total, 0, ',', '.') ?>đ</strong>
                                </div>
                                <?php else: ?>
                                <div class="cdp-empty">
                                    <i class="fa-solid fa-box-open"></i>
                                    <p>Giỏ hàng trống</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <script>
                        const cartWrapper = document.querySelector('.cart-dropdown-wrapper');
                        const cartPanel = document.getElementById('cart-dropdown-panel');
                        const cartBtn = document.getElementById('cart-toggle-btn');

                        if (cartWrapper && cartPanel && cartBtn) {

                            // DESKTOP: hover vào wrapper thì mở, rời ra thì đóng
                            cartWrapper.addEventListener('mouseenter', function() {
                                cartPanel.classList.add('open');
                            });
                            cartWrapper.addEventListener('mouseleave', function() {
                                cartPanel.classList.remove('open');
                            });

                            // MOBILE: chạm vào icon thì toggle dropdown
                            // Chạm lần 1 = mở dropdown, chạm lần 2 = vào trang cart
                            let touchOpenedOnce = false;
                            cartBtn.addEventListener('click', function(e) {
                                const isTouchDevice = window.matchMedia('(hover: none)').matches;

                                if (isTouchDevice) {
                                    if (!cartPanel.classList.contains('open')) {
                                        // Lần chạm đầu: chỉ mở dropdown, chưa chuyển trang
                                        e.preventDefault();
                                        cartPanel.classList.add('open');
                                    }
                                    // Lần chạm thứ 2: để href chạy tự nhiên -> vào ?page=cart
                                }
                            });

                            // Chạm ra ngoài thì đóng dropdown (mobile)
                            document.addEventListener('click', function(e) {
                                if (!cartWrapper.contains(e.target)) {
                                    cartPanel.classList.remove('open');
                                }
                            });
                        }
                    </script>
                        <!-- <a href="?page=order_history" class="action-btn order-btn" title="Lịch sử đặt hàng">
                            <i class="fa-solid fa-clipboard-list"></i>
                            <?php 
                                if (isset($_SESSION['new_orders']) && $_SESSION['new_orders'] > 0) {
                                    echo '<span class="badge cart-badge" id="order-badge-count" style="background-color: #ff4757;">' . $_SESSION['new_orders'] . '</span>';
                                }
                            ?>
                        </a> -->
                </div>
            </div>   
        </div>
    </header>
    <div class="mobile-menu-overlay"></div>