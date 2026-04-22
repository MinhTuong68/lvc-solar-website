<nav class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <i class="fa-solid fa-bot-lightning"></i>
        <span>LVC SOLAR</span>
    </div>   
    <ul class="sidebar-menu">
        <!-- Tổng quan -->
        <li class="menu-title" style="margin-left: 5px;">TỔNG QUAN</li>
        <li style="margin-left: 20px;">
            <a href="index.php?page=manage-dashboard" class="<?= ($current_page == 'manage-dashboard')? 'active': ''?>">
                <i class="icon fa-solid fa-chart-line"></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        

        <!-- Sản phẩm -->
        <li class="menu-group active">
            <div class="menu-title toggle-group">
                Sản phẩm
                <span class="arrow">▾</span>  
            </div>
            <ul class="submenu">
                <li>
                    <a href="index.php?page=manage-products" class="<?= ($current_page == 'manage-products' || $current_page == 'add-products' || $current_page == 'edit-product')? 'active': ''?>">
                        <i class="icon fa-solid fa-cart-shopping"></i>
                        <span class="text">Sản phẩm</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=manage-category" class="<?= ($current_page == 'manage-category' || $current_page == 'edit-category')? 'active': ''?>">
                        <i class="icon fa-solid fa-layer-group"></i>
                        <span class="text">Doanh mục</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=manage-brands" class="<?= ($current_page == 'manage-brands')? 'active': ''?>">
                        <i class="icon fa-solid fa-trademark"></i>
                        <span class="text">Thương hiệu</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="icon fa-solid fa-star"></i>
                        <span class="text">Đánh giá sản phẩm</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Kinh doanh -->
        <li class="menu-group active">
            <div class="menu-title toggle-group">
                Kinh doanh
                <span class="arrow">▾</span>
            </div>
            <ul class="submenu">
                <li>
                    <a href="index.php?page=manage-order" class="<?= ($current_page == 'manage-order')? 'active': ''?>">
                        <i class="icon fa-solid fa-cart-arrow-down"></i>
                        <span class="text">Đơn hàng</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=manage-service" class="<?= ($current_page == 'manage-service')? 'active': ''?>">
                        <i class="icon fa-solid fa-screwdriver-wrench"></i>
                        <span class="text">Dịch dụ solar</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Nội dung -->
        <li class="menu-group active">
            <div class="menu-title toggle-group">
                Nội dung
                <span class="arrow">▾</span>
            </div>
            <ul class="submenu">
                <li>
                    <a href="index.php?page=manage-projects" class="<?= ($current_page == 'manage-projects')? 'active': ''?>">
                        <i class="icon fa-solid fa-bars-progress"></i>
                        <span class="text">Dự án</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=manage-news" class="<?= ($current_page == 'manage-news' || $current_page == 'add-news')? 'active': ''?>">
                        <i class="icon fa-solid fa-newspaper"></i>
                        <span class="text">Blog (SEO)</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=manage-contact" class="<?= ($current_page == 'manage-contact')? 'active': ''?>">
                        <i class="icon fa-solid fa-phone"></i>
                        <span class="text">Liên hệ</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Chăm sóc khách hàng -->
        <!-- <li class="menu-group active">
            <div class="menu-title toggle-group">
                Chăm sóc khách hàng
                <span class="arrow">▾</span>
            </div>
            <ul class="submenu">
                <li>
                    <a href="">
                        <i class="icon fa-solid fa-comment-dots"></i>
                        <span class="text">Tin nhắn người dùng</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="icon fa-solid fa-message"></i>
                        <span class="text">Phản hồi người dùng</span>
                    </a>
                </li>
            </ul>
        </li> -->

        <!-- Marketing & Khuyến mãi -->
        <li class="menu-group active">
            <div class="menu-title toggle-group">
                Marketing & Khuyến mãi
                <span class="arrow">▾</span>
            </div>
            <ul class="submenu">
                <li>
                    <a href="">
                        <i class="icon fa-solid fa-tag"></i>
                        <span class="text">Tạo mã giảm giá</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="icon fa-solid fa-crown"></i>
                        <span class="text">Khách hàng thân thiết</span>
                    </a>
                </li>
            </ul>
        </li>

         <!-- Người dùng -->
        <li class="menu-group active">
            <div class="menu-title toggle-group">
                Quản lý tài khoản
                <span class="arrow">▾</span>
            </div>
            <ul class="submenu">
                <li>
                    <a href="">
                        <i class="icon fa-solid fa-user"></i>
                        <span class="text">Quản lý admin</span>
                    </a>
                </li>
                <!-- <li>
                    <a href="">
                        <i class="icon fa-solid fa-users"></i>
                        <span class="text">Quản lý người dùng</span>
                    </a>
                </li> -->
            </ul>
        </li>


        <!-- Hệ thống -->
         <li class="menu-group active">
            <div class="menu-title toggle-group">
                Hệ thống
                <span class="arrow">▾</span>
            </div>
            <ul class="submenu">
                <li>
                    <a href="">
                        <i class="icon fa-solid fa-gear"></i>
                        <span class="text">Cấu hình web</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="icon fa-solid fa-right-from-bracket"></i>
                        <span class="text">Đăng xuất</span>
                    </a>
                </li>
            </ul>
        </li>

       
    </ul>
</nav>