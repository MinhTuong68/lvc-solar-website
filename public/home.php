<?php
    // Gọi Class Service để lấy dữ liệu từ Database
    // (Đảm bảo đường dẫn này đúng với cấu trúc thư mục của bạn)
    include("../classes/services.php");
    include("../classes/news.php");
    include("../classes/projects.php");
    $project_obj = new Project($conn);
    $home_projects = $project_obj->getPopularProjects(9);
    $news_obj = new News($conn);
    $popularNews = $news_obj->getPopularNews(6);
    // Khởi tạo đối tượng (biến $conn đã được nối từ file index.php)
    $service_obj = new Service($conn);
    
    // Lấy danh sách dịch vụ gán vào biến $allTypes
    $allTypes = $service_obj->getAllServiceTypes();
    $sql_cat = "SELECT * FROM tbl_categories WHERE status = 1 LIMIT 5";
    $res_cat = mysqli_query($conn, $sql_cat);

    $sql_prod = "SELECT p.*, c.name AS category_name
             FROM tbl_products p
             LEFT JOIN tbl_categories c ON p.category_id = c.id
             WHERE p.status = 1 
             ORDER BY p.sold DESC 
             LIMIT 8";
    $res_prod = mysqli_query($conn, $sql_prod);

    if (!$res_prod) {
        echo '<p style="color:red">SQL Error: ' . mysqli_error($conn) . '</p>';
    }
?>
<section class="section_slider">
    <div class="container-slider">
        <div class="banner-slider">
            <div class="slider-track" id="bannerTrack">
                <div class="slide"><img src="../webctylvc/uploads/web/banner/banner6.jpg" alt="Banner LVC 1"></div>
                <div class="slide"><img src="https://globalenergy.vn/wp-content/uploads/2024/02/dien-nang-luong-mat-troi-va-ung-dung.jpg" alt="Banner LVC 2"></div>
                <div class="slide"><img src="../webctylvc/uploads/web/banner/lvc2.jpg" alt="Banner LVC 3"></div>
                <div class="slide"><img src="https://unisolar.com.vn/wp-content/uploads/2024/06/nang-luong-mat-troi-202309171559573222.jpg" alt="Banner LVC 3"></div>
            </div>
            
            <button class="slider-btn prev-btn" onclick="moveSlide(-1)"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="slider-btn next-btn" onclick="moveSlide(1)"><i class="fa-solid fa-chevron-right"></i></button>
            
            <div class="slider-dots">
                <span class="dot active" onclick="currentSlide(0)"></span>
                <span class="dot" onclick="currentSlide(1)"></span>
                <span class="dot" onclick="currentSlide(2)"></span>
            </div>
        </div>
    </div>
</section>

<section class="hero">
    <div class="container">
        <div class="hero-grid">
            <!-- Left Content -->
            <div class="hero-content">
                <span class="hero-eyebrow">
                    <i class="fa-solid fa-bolt"></i>
                    #1 Điện Mặt Trời Tại Bạc Liêu
                </span>
                <h1 class="hero-title">
                    Kiến Tạo<br>
                    Nguồn Năng<br>
                    Lượng <span>Xanh</span>
                </h1>
                <p class="hero-desc">
                    Tiết kiệm 100% hóa đơn tiền điện — Đầu tư thông minh cho gia đình và doanh nghiệp.
                    Hệ thống lắp đặt bởi đội ngũ kỹ thuật giàu kinh nghiệm, bảo hành 25 năm.
                </p>

                <div class="hero-actions">
                    <a href="#" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-calendar-check"></i> Đặt lịch khảo sát
                    </a>
                    <a href="?page=products" class="btn btn-outline btn-lg">
                        <i class="fa-solid fa-solar-panel"></i> Xem sản phẩm
                    </a>
                </div>

                <div class="hero-stats">
                    <div>
                        <span class="hero-stat-num">500+</span>
                        <span class="hero-stat-lbl">Công trình</span>
                    </div>
                    <div>
                        <span class="hero-stat-num">25</span>
                        <span class="hero-stat-lbl">Năm bảo hành</span>
                    </div>
                    <div>
                        <span class="hero-stat-num">100%</span>
                        <span class="hero-stat-lbl">Hài lòng</span>
                    </div>
                </div>
            </div>

            <!-- Right: Quick Booking Form -->
            <div class="hero-card">
                <h3>☀ YÊU CẦU <span>BÁO GIÁ MIỄN PHÍ</span></h3>
                <form id="hero-form" action="actions/add-home.php" method="POST">
                    <div class="hero-form-group">
                        <input type="text" name="customer_name" placeholder="Họ và tên của bạn *" required>
                    </div>

                    <div class="hero-form-group">
                        <input type="tel" name="phone" placeholder="Số điện thoại *" required>
                    </div>

                    <div class="hero-form-group">
                        <input type="text" name="address" placeholder="Địa chỉ lắp đặt">
                    </div>

                    <div class="hero-form-group">
                        <select name="service_type_id">
                            <option value="">-- Chọn dịch vụ cần tư vấn --</option>
                            <?php 
                                // Tự động in danh sách dịch vụ từ Database ra đây
                                if(!empty($allTypes)){
                                    foreach($allTypes as $type){
                                        // value là ID (để lưu DB), hiển thị chữ (để khách đọc)
                                        echo '<option value="'.$type['id'].'">'.htmlspecialchars($type['service_type']).'</option>';
                                    }
                                }
                            ?>
                        </select>
                    </div>

                     <div class="hero-form-group">
                        <input type="text" name="electricity_bill" placeholder="Tiền điện TB/tháng (VD: 1.500.000đ)">
                    </div>
                    <div class="hero-form-group">
                        <textarea name="issue_description" placeholder="Ghi chú yêu cầu thêm..."></textarea>
                    </div>
                    <button type="submit" name="btn_submit_quote" class="btn btn-amber btn-block btn-lg">
                        GỬI YÊU CẦU <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<div class="trust-bar">
    <div class="container">
        <div class="trust-inner">
            <div class="trust-item">
                <div class="icon"><i class="fa-solid fa-shield-halved"></i></div>
                <div>
                <strong>Bảo hành 25 năm</strong>
                <div style="font-size:.75rem;color:rgba(255,255,255,.5)">Tấm pin chính hãng</div>
                </div>
            </div>
            <div class="trust-divider"></div>

            <div class="trust-item">
                <div class="icon"><i class="fa-solid fa-certificate"></i></div>
                <div>
                <strong>Chứng nhận EVN</strong>
                <div style="font-size:.75rem;color:rgba(255,255,255,.5)">Đủ điều kiện hòa lưới</div>
                </div>
            </div>
            <div class="trust-divider"></div>

            <div class="trust-item">
                <div class="icon"><i class="fa-solid fa-truck-fast"></i></div>
                <div>
                <strong>Khảo sát miễn phí</strong>
                <div style="font-size:.75rem;color:rgba(255,255,255,.5)">Toàn tỉnh Bạc Liêu</div>
                </div>
            </div>
            <div class="trust-divider"></div>

            <div class="trust-item">
                <div class="icon"><i class="fa-solid fa-headset"></i></div>
                <div>
                <strong>Hỗ trợ 24/7</strong>
                <div style="font-size:.75rem;color:rgba(255,255,255,.5)">Kỹ thuật viên tận tâm</div>
                </div>
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
                <div class="icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                <div>
                <strong>Hỗ trợ trả góp</strong>
                <div style="font-size:.75rem;color:rgba(255,255,255,.5)">Lãi suất 0% (12 tháng)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    // 1. Lấy sản phẩm thuộc danh mục "Gói năng lượng mặt trời" (Thay số 2 bằng ID thực tế của bạn)
    $id_danh_muc_goi = 24; 
    $sql_hot_sale = "SELECT * FROM tbl_products WHERE category_id = $id_danh_muc_goi AND status = 1 ORDER BY sold DESC LIMIT 8";
    $res_hot_sale = mysqli_query($conn, $sql_hot_sale);
?>

<section class="pd-section hot-sale-section" style="padding: 40px 0; background-color: #f8fafc;">
    <div class="container">
        <div class="section-header" style="text-align: center; margin-bottom: 30px;">
            <span class="section-label" style="color: #0284c7; font-weight: bold; font-size: 1.2rem;">
                <i class="fa-solid fa-solar-panel"></i> Giải Pháp Toàn Diện
            </span>
            <h2 class="section-title">Các gói năng lượng mặt trời được nhiều khách lựa chọn</h2>
        </div>

        <div class="hot-sale-slider">
            <?php 
                if ($res_hot_sale && mysqli_num_rows($res_hot_sale) > 0){
                ?>
                    <?php 
                        while ($row = mysqli_fetch_assoc($res_hot_sale)): 
                        ?>
                    
                            <div class="hot-sale-card">
                                <div class="card-img">
                                    <img src="uploads/products/images/<?= $row['image'] ?>" alt="<?= $row['name'] ?>">
                                </div>
                                
                                <div class="card-info">
                                    <h3 class="product-name"><a href="index.php?page=detail_product&id=<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a></h3>
                                    <span class="product-power"><i class="fa-solid fa-bolt product-power-icon"></i> <?php echo $row['power_capacity'] ?></span>
                                    
                                    <div class="card-actions">
                                        <?php
                                            if($row['price']>0){
                                                ?>
                                                     <button class="btn-cart" onclick="addToCart(<?= $row['id'] ?>)" title="Thêm vào giỏ">
                                                        <i class="fa-solid fa-cart-plus"></i>
                                                    </button>
                                                <?php
                                            }else{
                                                ?>
                                                    <a href="tel:0945671536" class="btn btn-primary btn-lg"><i class="fa-solid fa-phone-volume"></i> Gọi ngay</a>
                                                <?php
                                            }
                                        ?>
                                       
                                        <a href="?page=contact" class="btn-consult">Nhận tư vấn</a>
                                    </div>
                                </div>
                            </div>
                        <?php 
                        endwhile; 
                    ?>
                <?php
            }
             else{
                ?>
                <p style="text-align: center; width: 100%;">Đang cập nhật các gói năng lượng...</p>
                <?php
            }
            ?>
        </div>
    </div>
</section>

<section class="home-categories">
    <div class="container">
        <div class="section-header" style="margin-bottom: 2rem;">
            <h2 class="section-title" style="font-size: 1.8rem;">Danh Mục Sản Phẩm</h2>
        </div>
        <div class="cat-grid">
            <?php 
                if($res_cat && mysqli_num_rows($res_cat) > 0){
                    while($cat = mysqli_fetch_assoc($res_cat)){
                        $cat_img = !empty($cat['image']) ? '../webctylvc/uploads/categories/'.$cat['image'] : 'https://cdn-icons-png.flaticon.com/512/3255/3255014.png';
                        ?>
                            <a href="index.php?page=products&cat=<?= $cat['id'] ?>" class="cat-box">
                                <span class="cat-name"><?= htmlspecialchars($cat['name']) ?></span>
                            </a>
                        <?php 
                    }
                }else{
                    echo "<p>Chưa có danh mục nào.</p>";
                }
            ?>
        </div>
    </div>
</section>


<section class="pd-section" style="padding: 10px 0; background-color: white;">
    <div class="container">
        <div class="section-header">
            <span class="section-label" style="color: #dc2626;"><i class="fa-solid fa-fire"></i> Hot Sale</span>
            <h2 class="section-title">Sản Phẩm Bán Chạy</h2>
        </div>
        
        <div class="grid-4">
            <?php 
            if($res_prod && mysqli_num_rows($res_prod) > 0){
                while($row = mysqli_fetch_assoc($res_prod)){
                    // ĐÃ SỬA: Lấy đúng tên cột giá từ DB của bạn (price và old_price)
                    $price = $row['price'] ?? 0;
                    $old_price = $row['old_price'] ?? 0;
                    
                    // Tính phần trăm giảm giá tự động
                    $percent = 0;
                    if($old_price > 0 && $price > 0 && $price < $old_price) {
                        $percent = round((($old_price - $price) / $old_price) * 100);
                    }
                    if($price>0 && $row['category_id'] != 24){
                        ?>
                            <div class="product-card">
                                <div class="product-img-wrap">
                                    <?php 
                                        if($percent > 0){
                                        ?>
                                            <span class="product-badge badge-hot">-<?= $percent ?>%</span>
                                        <?php 
                                        }
                                        else{
                                        ?>
                                            <span class="product-badge badge-new">Mới</span>
                                        <?php
                                        }
                                    ?>
                                    
                                    <img src="<?= ROOT_URL ?>/uploads/products/images/<?= $row['image'] ?? 'default.jpg' ?>" alt="<?= $row['name'] ?>">
                                    </div>
                                    
                                    <div class="product-body">
                                        <span class="product-cat"><?= $row['category_name'] ?? 'Chưa phân loại' ?></span>
                                        <h3 class="product-name"><a href="index.php?page=detail_product&id=<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a></h3>
                                        <div class="product-power"><i class="fa-solid fa-bolt product-power-icon"></i> <?= $row['power_capacity'] ?? 'N/A' ?></div>
                                        
                                        <div class="product-price-row">
                                            <div>
                                                <?php 
                                                    if($percent > 0){
                                                        ?>
                                                            <div class="product-old-price"><?= number_format($old_price, 0, ',', '.') ?>đ</div>
                                                            <div class="product-price"><?= number_format($price, 0, ',', '.') ?>đ</div>
                                                        <?php 
                                                    }else{
                                                        ?>
                                                            <h3>Liên hệ báo giá</h3>
                                                        <?php
                                                    }
                                                ?>
                                            </div>
                                        </div>
                                        
                                        <div class="product-actions">
                                            <button class="btn btn-ghost" title="Thêm giỏ hàng" onclick="addToCartAjax(<?= $row['id'] ?>, 1, 'add_cart')"><i class="fa-solid fa-cart-plus"></i></button>
                                            <button class="btn btn-amber" onclick="addToCartAjax(<?= $row['id'] ?>, 1, 'buy_now')">Mua ngay</button>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                    }
                }
            }
            else{
                echo "<p style='grid-column: 1/-1; text-align: center;'>Chưa có sản phẩm nào được hiển thị.</p>";
            }
            ?>
        </div><br><br>
         <div class="see-more"><br><br>
            <a href="?page=products" class="btn-see-more">Xem thêm <i class="fa-solid fa-chevron-right"></i></a>
        </div><br>
    </div>
</section>

<section class="services-section">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Dịch vụ của chúng tôi</span>
            <h2 class="section-title">Giải Pháp năng lượng mặt trời</h2>
            <p class="section-sub">Từ tư vấn, thiết kế đến thi công và bảo trì — LVC Solar đồng hành cùng bạn suốt vòng đời hệ thống.</p>
        </div>
        <div class="grid-4">
            <div class="service-card animate-on-scroll">
                <div class="service-icon-wrap">⚡</div>
                <h4>Lắp Đặt Trọn Gói</h4>
                <p>Khảo sát, thiết kế và thi công hệ thống điện mặt trời áp mái hòa lưới, bám tải, lưu trữ pin.</p>
                <a href="?page=services#lap-dat">Xem chi tiết <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="service-card animate-on-scroll">
                <div class="service-icon-wrap">💧</div>
                <h4>Vệ Sinh Tấm Pin</h4>
                <p>Vệ sinh chuyên nghiệp bằng máy móc hiện đại, đảm bảo tối đa hiệu suất phát điện quanh năm.</p>
                <a href="?page=services#ve-sinh">Xem chi tiết <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="service-card animate-on-scroll">
                <div class="service-icon-wrap">🔧</div>
                <h4>Bảo Trì & Sửa Chữa</h4>
                <p>Kiểm tra định kỳ, chẩn đoán lỗi và sửa chữa nhanh chóng giúp hệ thống luôn hoạt động ổn định.</p>
                <a href="?page=services#bao-tri">Xem chi tiết <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="service-card animate-on-scroll">
                <div class="service-icon-wrap">🚀</div>
                <h4>Nâng Cấp Hệ Thống</h4>
                <p>Nâng cấp inverter, bổ sung pin lưu trữ Lithium, mở rộng công suất cho hộ gia đình và doanh nghiệp.</p>
                <a href="?page=services#nang-cap">Xem chi tiết <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<section class="process-section">
    <div class="container">
        <div class="section-header">
            <span class="section-label" style="color:var(--amber)">Quy trình làm việc</span>
            <h2 class="section-title">4 Bước Đơn Giản</h2>
            <p class="section-sub" style="margin:0 auto">Từ lúc liên hệ đến khi hệ thống phát điện, LVC Solar đảm bảo quy trình chuyên nghiệp, minh bạch.</p>
        </div>
        <div class="grid-4">
            <div class="step-card animate-on-scroll">
                <div class="step-num">01</div>
                <h4>Liên hệ & Khảo sát</h4>
                <p>Đội kỹ thuật đến tận nơi khảo sát miễn phí, đánh giá mái nhà và tư vấn công suất phù hợp.</p>
            </div>
            <div class="step-card animate-on-scroll">
                <div class="step-num">02</div>
                <h4>Thiết kế & Báo giá</h4>
                <p>Lập phương án thiết kế chi tiết, báo giá minh bạch, không phát sinh chi phí ẩn.</p>
            </div>
            <div class="step-card animate-on-scroll">
                <div class="step-num">03</div>
                <h4>Thi công & Lắp đặt</h4>
                <p>Thi công nhanh chóng 1–3 ngày, đảm bảo tiêu chuẩn kỹ thuật và an toàn lao động.</p>
            </div>
            <div class="step-card animate-on-scroll">
                <div class="step-num">04</div>
                <h4>Nghiệm thu & Bảo hành</h4>
                <p>Hướng dẫn sử dụng, đăng ký hòa lưới EVN, bảo hành 25 năm tấm pin + 5 năm inverter.</p>
            </div>
        </div>
    </div>
</section>

<section style="backgroud-color: #f8fafc">
  <div class="container">
    <div class="why-grid">
      <div class="why-img-wrap">
        <img src="https://images.unsplash.com/photo-1509391366360-2e959784a276?q=80&w=800" alt="LVC Solar">
        <div class="why-badge">
          <div class="num">500+</div>
          <p>Công trình đã thi công</p>
        </div>
      </div>
      <div>
        <span class="section-label">Vì sao chọn LVC Solar?</span>
        <h2 class="section-title" style="margin-bottom:1.5rem">Cam Kết Chất Lượng<br>Hàng Đầu Bạc Liêu</h2>
        <div class="why-items">
          <div class="why-item animate-on-scroll">
            <div class="why-item-icon">🏆</div>
            <div>
              <h4>Kinh nghiệm 10+ năm</h4>
              <p>Đội ngũ kỹ thuật viên được đào tạo chuyên sâu, có chứng chỉ hành nghề điện.</p>
            </div>
          </div>
          <div class="why-item animate-on-scroll">
            <div class="why-item-icon">⚡</div>
            <div>
              <h4>Vật tư chính hãng 100%</h4>
              <p>Phân phối trực tiếp từ nhà sản xuất: JinkoSolar, Growatt, Deye, Canadian Solar.</p>
            </div>
          </div>
          <div class="why-item animate-on-scroll">
            <div class="why-item-icon">💰</div>
            <div>
              <h4>Giá cạnh tranh, minh bạch</h4>
              <p>Báo giá chi tiết không phát sinh, hỗ trợ vay vốn 0% lãi suất 12 tháng.</p>
            </div>
          </div>
          <div class="why-item animate-on-scroll">
            <div class="why-item-icon">🛡️</div>
            <div>
              <h4>Bảo hành toàn diện</h4>
              <p>Bảo hành 25 năm tấm pin, 5 năm inverter, 2 năm thi công lắp đặt.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section style="background: #f8fafc;">
    <div class="container">
        <div class="home-project-header">
            <span class="home-project-subtitle">Công Trình Tiêu Biểu</span>
            <h2 class="home-project-title">Dự án đã thi công</h2>
            <p class="home-project-desc">Hàng trăm hệ thống điện mặt trời được LVC Solar thi công hoàn thiện, mang lại giải pháp tiết kiệm điện tối ưu cho gia đình và doanh nghiệp.</p>
        </div>
        <div class="home-project-grid">
            <?php
                if (!empty($home_projects)){
                    foreach ($home_projects as $p){
                        ?>  
                            <div class="home-project-card"> 
                                <div class="home-project-img-wrap">
                                    <a href="?page=detail_project&slug=<?= !empty($p['slug']) ? e($p['slug']) : $p['id'] ?>">
                                        <img src="<?= ROOT_URL ?>uploads/projects/images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                                    </a> 
                                    <div class="home-project-badges">
                                        <?php
                                            if ($p['is_featured'] == 1){
                                                ?>
                                                    <span class="badge-hot"><i class="fa-solid fa-star"></i> Tiêu biểu</span>
                                                <?php
                                            }
                                            if(!empty($p['capacity'])){
                                                ?>
                                                    <span class="badge-capacity"><i class="fa-solid fa-bolt"></i> <?= htmlspecialchars($p['capacity']) ?></span>
                                                <?php
                                            }
                                        ?>
                                    </div>
                                </div>         

                                <div class="home-project-info">
                                    <a href="?page=detail_project&slug=<?= !empty($p['slug']) ? e($p['slug']) : $p['id'] ?>">
                                        <h3 class="home-project-name">
                                            <?= htmlspecialchars($p['name']) ?>
                                        </h3>
                                    </a>
                                    
                                    <div class="project-meta">
                                        <?php
                                            if (!empty($p['location'])){
                                                ?>
                                                    <span><i class="fa-solid fa-location-dot"></i> <?php echo $p['location'] ?></span>
                                                <?php
                                            }
                                            if (!empty($p['capacity'])){
                                                ?>
                                                    <span><i class="fa-solid fa-bolt"></i> <?php echo $p['capacity'] ?></span>
                                                <?php
                                            }
                                            if (($p['views']) >= 0){
                                                ?>
                                                    <span><i class="fa-solid fa-eye"></i> <?php echo $p['views'] ?></span>
                                                <?php
                                            }
                                        ?>
                                    </div>
                                    <div class="home-project-footer">
                                        <span class="home-project-readmore">Xem chi tiết <i class="fa-solid fa-arrow-right-long"></i></span>
                                        <span class="home-project-date">
                                            <?php 
                                                if (!empty($p['completion_date'])) {
                                                    echo date('d/m/Y', strtotime($p['completion_date']));
                                                } else {
                                                    echo date('d/m/Y', strtotime($p['created_at']));
                                                }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>    
                        <?php
                    }
                }
            ?>
        </div><br><br>
        <div class="text-center">
            <a href="?page=projects" class="btn btn-ghost btn-lg-blog">Xem tất cả dự án <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- ── CTA ────────────────────────────────────────────────── -->
<section class="cta-section">
  <div class="container">
    <h2>Sẵn sàng tiết kiệm tiền điện?</h2>
    <p>Khảo sát miễn phí — Báo giá trong 24 giờ — Thi công chuyên nghiệp</p>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
      <a href="?page=services#booking" class="btn btn-navy btn-lg">
        <i class="fa-solid fa-calendar-check"></i> Đặt lịch khảo sát miễn phí
      </a>
      <a href="tel:0912345678" class="btn btn-outline btn-lg">
        <i class="fa-solid fa-phone"></i> Gọi ngay: 0912.345.678
      </a>
    </div>
  </div>
</section>

<section style="background:var(--white)">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Kiến thức Solar</span>
            <h2 class="section-title">Blog & Tin Tức</h2>
            <p class="section-sub">Cập nhật kiến thức mới nhất về năng lượng mặt trời, chính sách điện, và công nghệ xanh.</p>
        </div>
        <div class="grid-3">
            <?php
                if(!empty($popularNews)){
                    foreach($popularNews as $n){
                        ?>
                            <div class="blog-card animate-on-scroll">
                                <a href="?page=news_detail&slug=<?php echo $n['slug'] ?>">
                                    <img src="<?php echo ROOT_URL ?>/uploads/news/images/<?= htmlspecialchars($n['image']) ?>" alt="<?= htmlspecialchars($n['title']) ?>" loading="lazy">
                                </a>
                                <div class="blog-body">
                                    <div class="blog-meta">
                                        <span class="tag">Kiến thức</span>
                                        <span><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y', strtotime($n['created_at'])) ?></span>
                                        <span><i class="fa-solid fa-eye"></i> <?= $n['views'] ?></span>
                                    </div>
                                    <a href="?page=news_detail&slug=<?= $n['slug'] ?>">
                                        <h4>  <?= htmlspecialchars($n['title']) ?></h4>
                                    </a>
                                    <p>
                                        <?php
                                            $clean_text = strip_tags(html_entity_decode($n['content']));
                                            echo mb_strimwidth($clean_text, 0, 50, "...");
                                        ?>
                                    </p>
                                    <a href="?page=news_detail&slug=<?= $n['slug'] ?>" class="read-more">
                                        Đọc thêm <i class="fa-solid fa-arrow-right-long"></i>
                                    </a>
                                </div>
                            </div>
                        <?php
                    }
                }
                else{
                    ?>
                        <p style="color: var(--gray-400); font-size: 0.85rem; text-align: center;">Chưa có bài viết nào.</p>
                    <?php
                }
            ?>
        </div><br><br>
         <div style="text-align:center;">
            <a href="?page=blog.php" class="btn btn-ghost btn-lg-blog">Xem tất cả bài viết</a>
        </div>
    </div>
</section>

<section class="contact-section" id="contact">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <span class="section-label">Liên hệ ngay</span>
                <h2>Tư Vấn Miễn Phí<br>Ngay Hôm Nay</h2>
                <p>Để lại thông tin và chúng tôi sẽ liên hệ lại trong vòng 30 phút. Đội ngũ kỹ thuật viên giàu kinh nghiệm sẵn sàng tư vấn giải pháp phù hợp nhất cho bạn.</p>
                <div class="contact-item">
                    <div class="icon"><i class="fa-solid fa-location-dot"></i></div>
                    <div>
                        <h4>Địa chỉ</h4>
                        <p>123 Đường Công Nghệ, TP. Bạc Liêu, Tỉnh Bạc Liêu</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="icon"><i class="fa-solid fa-phone"></i></div>
                    <div>
                        <h4>Hotline kỹ thuật</h4>
                        <p>0912.345.678 (Mr. Tường)</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="icon"><i class="fa-solid fa-envelope"></i></div>
                    <div>
                        <h4>Email</h4>
                        <p>tuongpm@ctylvc.com.vn</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="icon"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <h4>Giờ làm việc</h4>
                        <p>Thứ 2 – Thứ 7 · 8:00 – 17:30</p>
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <h3>Gửi Yêu Cầu Tư Vấn</h3>
                <form id="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Họ và tên <span>*</span></label>
                            <input type="text" name="fullname" class="form-control" placeholder="Nguyễn Văn A" required>
                        </div>
                        <div class="form-group">
                            <label>Số điện thoại <span>*</span></label>
                            <input type="tel" name="phone" class="form-control" placeholder="0912 345 678" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com">
                    </div>
                    <div class="form-group">
                        <label>Nội dung <span>*</span></label>
                        <textarea name="message" class="form-control" placeholder="Mô tả nhu cầu của bạn (muốn lắp đặt, công suất dự kiến, loại mái nhà...)" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fa-solid fa-paper-plane"></i> Gửi yêu cầu tư vấn
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>