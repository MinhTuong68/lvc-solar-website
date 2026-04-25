<?php
    // 1. GỌI CLASS VÀ LẤY SLUG TỪ URL
    include_once ('../classes/products.php');
    $productObj  = new Product($conn);

    // Hứng biến slug trên URL (ví dụ: ?page=product-detail&slug=tam-pin-canadian-550w)
    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
    $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // Nếu cả slug và id đều không có thì mới cho về trang sản phẩm
    if (empty($slug) && $id <= 0) {
        echo "<script>window.location.href='?page=products';</script>";
        exit;
    }

    // 2. TÌM SẢN PHẨM DỰA VÀO SLUG
    // (Tận dụng hàm getAllProducts đã có sẵn join Category và Brand của bạn)
    $allProducts = $productObj->getAllProducts();
    $product = null;

    foreach ($allProducts as $p) {
        if ($p['status'] == 1) {
            // Ưu tiên khớp Slug
            if (!empty($slug) && $p['slug'] === $slug) {
                $product = $p;
                break;
            }
            // Nếu không có slug trên URL, kiểm tra khớp ID
            if (empty($slug) && $id > 0 && $p['id'] == $id) {
                $product = $p;
                break;
            }
        }
    }

    // 3. XỬ LÝ NẾU SẢN PHẨM KHÔNG TỒN TẠI (URL bậy bạ hoặc đã bị Admin ẩn)
    if (!$product) {
        echo "<div class='container' style='padding: 100px 20px; text-align: center; min-height: 50vh;'>
                <i class='fa-solid fa-triangle-exclamation' style='font-size: 4rem; color: var(--amber); margin-bottom: 20px;'></i>
                <h2 style='color: var(--navy);'>Sản phẩm không tồn tại!</h2>
                <p style='color: var(--gray-600); margin-top: 10px;'>Sản phẩm này có thể đã ngừng kinh doanh hoặc đường dẫn không chính xác.</p>
                <a href='?page=products' class='btn btn-primary' style='margin-top: 20px;'>Quay lại Cửa hàng</a>
              </div>";
        return; // Dừng nạp phần HTML bên dưới
    }

    // 4. LẤY THƯ VIỆN ẢNH (GALLERY) CỦA SẢN PHẨM NÀY
    $galleries = [];
    $productId = $product['id'];
    
    // Viết câu query trực tiếp gọn nhẹ để lấy ảnh từ bảng tbl_product_gallery
    $sqlGallery = "SELECT image_path FROM tbl_product_gallery WHERE product_id = $productId ORDER BY sort_order ASC, id DESC";
    $resGallery = mysqli_query($conn, $sqlGallery);
    
    if ($resGallery && mysqli_num_rows($resGallery) > 0) {
        while ($row = mysqli_fetch_assoc($resGallery)) {
            $galleries[] = $row;
        }
    }
?>

<div class="breadcrumb-bar">
    <div class="container">
        <div class="breadcrumb">
            <a href="?page=home">Trang chủ</a><span class="sep">/</span>
            <a href="?page=products">Sản phẩm</a><span class="sep">/</span>
            <a href="?page=products" class="current">Sản phẩm</a>
        </div>
    </div>
</div>

<section class="pd-section">
    <div class="container">
        <div class="pd-overview-card">
            <div class="pd-grid">
                
               <div class="pd-gallery">
                    <div class="pd-main-img">
                        <?php 
                            if (!empty($product['video'])){
                                ?>
                                    <video id="pdMainVideo" src="<?= ROOT_URL ?>uploads/products/videos/<?= e($product['video']) ?>" controls muted playsinline style="display: none; width: 100%; height: 100%; outline: none; border-radius: var(--radius);"></video>
                                    
                                    <img id="pdMainImage" style="display: block;" src="<?= ROOT_URL ?>uploads/products/images/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" onerror="this.src='https://placehold.co/600x600/f1f5f9/94a3b8?text=Chua+co+anh';">
                                <?php 
                            }
                            else{
                                ?>
                                    <video id="pdMainVideo" controls style="display: none; width: 100%; height: 100%; outline: none; border-radius: var(--radius);"></video>
                                    
                                    <img id="pdMainImage" style="display: block;" src="<?= ROOT_URL ?>uploads/products/images/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" onerror="this.src='https://placehold.co/600x600/f1f5f9/94a3b8?text=Chua+co+anh';">
                                <?php 
                            }
                        ?>
                    </div>

                    <div class="pd-thumbs">
                        <?php if (!empty($product['video'])): ?>
                            <div class="pd-thumb-item video-thumb active" onclick="changeMainMedia(this, 'video', '<?= ROOT_URL ?>uploads/products/videos/<?= e($product['video']) ?>')">
                                <i class="fa-solid fa-circle-play video-play-icon"></i>
                                <video src="<?= ROOT_URL ?>uploads/products/videos/<?= e($product['video']) ?>#t=0.1" muted playsinline style="width: 100%; height: 100%; object-fit: cover; pointer-events: none;"></video>
                            </div>
                        <?php endif; ?>

                        <div class="pd-thumb-item <?= empty($product['video']) ? 'active' : '' ?>" onclick="changeMainMedia(this, 'image', '<?= ROOT_URL ?>uploads/products/images/<?= e($product['image']) ?>')">
                            <img src="<?= ROOT_URL ?>uploads/products/images/<?= e($product['image']) ?>" alt="Thumbnail" onerror="this.src='https://placehold.co/100x100/f1f5f9/94a3b8?text=No image';">
                        </div>
                        
                        <?php if (!empty($galleries)): foreach ($galleries as $gal): ?>
                            <div class="pd-thumb-item" onclick="changeMainMedia(this, 'image', '<?= ROOT_URL ?>uploads/products/image_gallery/<?= e($gal['image_path']) ?>')">
                                <img src="<?= ROOT_URL ?>uploads/products/image_gallery/<?= e($gal['image_path']) ?>" alt="Thumbnail" onerror="this.style.display='none';">
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <div class="pd-info">
                    <div class="pd-brand-cat">
                        <span class="pd-tag cat"><?= e($product['category_name'] ?? 'Chưa phân loại') ?></span>
                        <?php
                            if(!empty($product['brand_name']) && isset($product['brand_status']) && $product['brand_status'] == 1){
                                $brand_display = $product['brand_name'];
                            }
                            else{
                                $brand_display = 'Đang cập nhật';
                            }
                        ?>
                        <span class="pd-tag brand"><?php echo $brand_display ?></span>
                    </div>
                    
                    <h1 class="pd-title"><?= e($product['name']) ?></h1>
                    
                    <div class="pd-meta-row">
                        <div class="pd-meta-item">
                            <span class="label">Tình trạng:</span>
                            <?php if ($product['stock'] > 0): ?>
                                <span class="value text-green"><i class="fa-solid fa-check-circle"></i> Còn hàng (<?= $product['stock'] ?>)</span>
                            <?php else: ?>
                                <span class="value text-red"><i class="fa-solid fa-xmark-circle"></i> Hết hàng</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($product['warranty'])): ?>
                        <div class="pd-meta-item">
                            <span class="label">Bảo hành:</span>
                            <span class="value"><i class="fa-solid fa-shield-halved"></i> <?= e($product['warranty']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="pd-price-box">
                        <?php if ($product['price'] > 0): ?>
                            <div class="pd-price-current"><?= number_format($product['price'], 0, ',', '.') ?>đ</div>
                            <?php if ($product['old_price'] > 0): ?>
                                <div class="pd-price-old"><?= number_format($product['old_price'], 0, ',', '.') ?>đ</div>
                                <?php $discount = round((($product['old_price'] - $product['price']) / $product['old_price']) * 100); ?>
                                <div class="pd-discount-badge">-<?= $discount ?>%</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="pd-price-current contact">Liên hệ báo giá</div>
                        <?php endif; ?>
                    </div>

                    <div class="pd-short-desc">
                        <?= !empty($product['short_description']) ? nl2br(e($product['short_description'])) : 'Sản phẩm năng lượng mặt trời chính hãng phân phối bởi LVC Solar.' ?>
                    </div>

                    <hr class="pd-divider">

                    <form method="POST" id="mainCartForm" class="pd-action-form">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        
                        <div class="pd-quantity-wrap">
                            <label>Số lượng:</label>
                            <div class="pd-qty-control">
                                <button type="button" class="qty-btn" onclick="updateQty(-1)"><i class="fa-solid fa-minus"></i></button>
                                <input type="number" id="pdQuantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                                <button type="button" class="qty-btn" onclick="updateQty(1)"><i class="fa-solid fa-plus"></i></button>
                            </div>
                        </div>

                        <div class="pd-action-buttons">
                            <?php if ($product['price'] > 0 && $product['stock'] > 0): ?>
                                <button type="button" onclick="addToCartAjax(<?= $product['id'] ?>, document.getElementById('pdQuantity').value, 'add_cart')" class="btn btn-outline btn-lg pd-btn-add">
                                    <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ hàng
                                </button>
                                <button type="button" onclick="addToCartAjax(<?= $product['id'] ?>, document.getElementById('pdQuantity').value, 'buy_now')" class="btn btn-primary btn-lg pd-btn-buy">
                                    Mua ngay
                                </button>
                            <?php else: ?>
                                <a href="?page=contact" class="btn btn-amber btn-lg pd-btn-buy" style="width: 100%; display: flex; justify-content: center; align-items: center;">
                                    <i class="fa-solid fa-phone-volume"></i> Nhận tư vấn & Báo giá
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                    <div class="pd-trust-box">
                        <div class="trust-item"><i class="fa-solid fa-truck-fast"></i> Giao hàng toàn quốc</div>
                        <div class="trust-item"><i class="fa-solid fa-medal"></i> Cam kết chính hãng</div>
                        <div class="trust-item"><i class="fa-solid fa-headset"></i> Hỗ trợ kỹ thuật 24/7</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pd-content-card">
            <h3 class="pd-content-title">Chi tiết sản phẩm</h3>
            <div class="pd-tab-pane active" id="tab-desc">
                <div class="pd-desc-content">
                    <?php 
                        // Kiểm tra xem có nội dung không, có thì in ra, không thì báo trống
                        if (!empty($product['content'])) {
                            // In nội dung ra (Không dùng hàm e() để giữ nguyên các thẻ HTML in đậm, in nghiêng, hình ảnh từ CKEditor)
                            echo $product['content']; 
                        } else {
                            echo "<p>Chưa có bài viết mô tả cho sản phẩm này.</p>";
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="pd-content-card">
            <div class="card-flex" style="justify-content: space-between;">
                <?php
                    if(!empty($product)){
                        ?>  
                            <div class="card-flex" style="gap: 10px;">
                                <div class="card-item">
                                    <img class="card-item-img" src="<?= ROOT_URL ?>uploads/products/images/<?= e($product['image']) ?>" alt="Thumbnail" onerror="this.src='https://placehold.co/100x100/f1f5f9/94a3b8?text=No image';">
                                </div>

                                <div>
                                    <div class="card-info">
                                        <h3 class="card-product-title"><?php echo $product['name'] ?></h3>
                                    </div>           
                                    <div>
                                        <?php 
                                            if ($product['old_price'] > 0){
                                                ?>
                                                    <div class="card-flex" style="gap: 10px;">
                                                        <?php
                                                            ?>
                                                                <div class="card-price-old">
                                                                    <?= number_format($product['old_price'], 0, ',', '.') ?>đ
                                                                </div>
                                                            <?php
                                                            $discount = round((($product['old_price'] - $product['price']) / $product['old_price']) * 100); 
                                                            ?>
                                                                <div class="pd-discount-badge">
                                                                    -<?= $discount ?>%
                                                                </div>
                                                            <?php
                                                        ?>
                                                    </div>
                                                    
                                                <?php
                                            }
                                            if ($product['price'] > 0){
                                                ?>
                                                    <div class="card-price-current">
                                                        <?= number_format($product['price'], 0, ',', '.') ?>đ
                                                    </div>
                                                <?php   
                                                
                                            }
            
                                            else{
                                                ?>
                                                    <div><p class="card-contact">Liên hệ báo giá</p></div>
                                                <?php 
                                            }
                                        ?>
                                    </div> 
                                </div>
                            </div>
                        <?php
                    }
                ?> 
                <?php
                    if($product['price'] > 0 && $product['stock'] > 0){
                        ?>
                            <div class="card-flex">    
                                <div class="card-flex" style="gap: 10px; align-items: center;">
                                    <div class="card-quantity-wrap">
                                        <label>Số lượng:</label>
                                        <div class="pd-qty-control">
                                            <button type="button" class="qty-btn" onclick="updateQty(-1,'pdQuantityButton')"><i class="fa-solid fa-minus"></i></button>
                                            <input type="number" id="pdQuantityButton" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                                            <button type="button" class="qty-btn" onclick="updateQty(1,'pdQuantityButton')"><i class="fa-solid fa-plus"></i></button>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button"  onclick="addToCartAjax(<?= $product['id'] ?>, document.getElementById('pdQuantityButton').value, 'add_cart')" class="btn-add-card">
                                            <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php
                    }
                    else{
                 
                    }
                ?>
            </div>
        </div>

        <div class="pd-content-card">
            <div class="abt-contact-grid">
                <div class="abt-contact-item">
                    <div class="abt-contact-icon">
                        <i class="fa-solid fa-car-side"></i>
                    </div>
                    <div class="abt-contact-val">
                        Giao hàng miễn phí toàn quốc (Kiểm tra hàng trước khi thanh toán)
                    </div>
                </div>

                <div class="abt-contact-item">
                    <div class="abt-contact-icon" style="background:#fef3c7;color:#d97706">
                        <i class="fa-solid fa-gears"></i>
                    </div>
                    <div class="abt-contact-val">
                        Lỗi kỹ thuật được đổi mới trong thời gian 30 ngày kể từ ngày nhận
                    </div>
                </div>

                <div class="abt-contact-item">
                    <div class="abt-contact-icon" style="background:#ffb8b8;color:#eb3b5a">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="abt-contact-val">
                        Bảo hành toàn quốc 3 năm về đèn & 5 năm tấm quang điện
                    </div>
                </div>

                <div class="abt-contact-item">
                    <div class="abt-contact-icon" style="background:#dbeafe;color:#2563eb">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div class="abt-contact-val">
                        Dịch vụ chăm sóc khách hàng miễn phí hỗ trợ 8h00 - 21h00 tất cả các ngày trong tuần
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const videoEl = document.getElementById('pdMainVideo');
        const imgEl = document.getElementById('pdMainImage');
        const videoThumb = document.querySelector('.video-thumb'); 

        // Kiểm tra xem sản phẩm này có video không
        if (videoThumb && videoEl && videoEl.getAttribute('src')) {
            
            // Hẹn giờ đúng 2 giây (2000 mili-giây)
            setTimeout(() => {
                // Chỉ tự phát nếu khách vẫn đang ở ô video (chưa lỡ tay bấm sang xem ảnh khác)
                if (videoThumb.classList.contains('active')) {
                    imgEl.style.display = 'none'; // Tắt ảnh tĩnh
                    videoEl.style.display = 'block'; // Mở video lên
                    
                    // Lệnh ép video tự chạy
                    videoEl.play().catch(error => {
                        console.log("Trình duyệt chặn tự động phát:", error);
                    });
                }
            }, 2000); 
        }
    });

    // Đổi Ảnh / Video mượt mà
    function changeMainMedia(thumbElement, type, mediaUrl) {
        const imgEl = document.getElementById('pdMainImage');
        const videoEl = document.getElementById('pdMainVideo');

        if (type === 'video') {
            // Tắt ảnh, bật video
            imgEl.style.display = 'none';
            videoEl.style.display = 'block';
            videoEl.src = mediaUrl;
            videoEl.play(); // Tự động phát khi bấm vào
        } else {
            // Tắt video, bật ảnh
            videoEl.style.display = 'none';
            videoEl.pause(); // Dừng video lại nếu đang phát
            imgEl.style.display = 'block';
            imgEl.src = mediaUrl;
        }

        // Bôi viền cam cho thumbnail đang chọn
        document.querySelectorAll('.pd-thumb-item').forEach(el => el.classList.remove('active'));
        thumbElement.classList.add('active');
    }

    function updateQty(change, inputId = 'pdQuantity') {
        let qtyInput = document.getElementById(inputId);
        if (!qtyInput) return; // Không tìm thấy ô thì dừng lại

        let currentVal = parseInt(qtyInput.value) || 1;
        
        // Sửa lỗi: Nếu DB chưa có tồn kho (max bị rỗng) thì cho mặc định max là 9999 để không bị liệt nút
        let maxStr = qtyInput.getAttribute('max');
        let maxVal = (maxStr && maxStr !== "") ? parseInt(maxStr) : 9999; 
        
        let newVal = currentVal + change;
        
        // Nếu số lượng hợp lệ thì mới cập nhật
        if (newVal >= 1 && newVal <= maxVal) {
            qtyInput.value = newVal;
        }
    }
</script>