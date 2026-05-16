<?php
    include_once ('../classes/products.php');
    include_once ('../classes/reviews.php');
    $productObj  = new Product($conn);
    $reviewObj   = new Review($conn);

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

    $reviewData = $reviewObj->getReviewsAndStatsByProduct($productId);
    $reviews = $reviewData['list'];
    $total_reviews = $reviewData['total'];
    $avg_rating = $reviewData['avg_rating'];
    $star_percents = $reviewData['star_percents'];

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
                <div class="pd-desc-content editor-content">
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

        <?php
            if($product['price']>0){
                ?>
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
                <?php
            }

        ?>

        <?php
            // LẤY DỮ LIỆU ĐÁNH GIÁ THỰC TẾ TỪ CLASS
            $reviewData = $reviewObj->getReviewsAndStatsByProduct($productId);
            $reviews = $reviewData['list'];
            $total_reviews = $reviewData['total'];
            $avg_rating = $reviewData['avg_rating'];
            $star_percents = $reviewData['star_percents'];
        ?>
        <div class="pd-content-card">
            <h3 class="pd-content-title">Đánh giá sản phẩm <?= e($product['name']) ?></h3>

            <!-- 1. TỔNG QUAN ĐÁNH GIÁ -->
            <div class="review-overview">
                <div class="rv-score-box">
                    <div class="rv-score"><?= $avg_rating > 0 ? number_format($avg_rating, 1) : '0.0' ?><span>/5</span></div>
                    <div class="rv-stars text-amber">
                        <?php 
                            for($i=1; $i<=5; $i++) {
                                echo $i <= round($avg_rating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star" style="color:var(--gray-300)"></i>';
                            }
                        ?>
                    </div>
                    <div class="rv-count"><?= $total_reviews ?> đánh giá</div>
                </div>
                
                <div class="rv-progress-box">
                    <?php for($i=5; $i>=1; $i--): ?>
                        <div class="rv-progress-item">
                            <span class="rv-star-label"><?= $i ?> <i class="fa-solid fa-star"></i></span>
                            <div class="rv-progress-bar"><div class="rv-progress-fill" style="width: <?= $star_percents[$i] ?>%;"></div></div>
                            <span class="rv-percent"><?= $star_percents[$i] ?>%</span>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="rv-action-box">
                    <button class="btn btn-primary btn-lg" onclick="document.getElementById('reviewModal').classList.add('active')">
                        <i class="fa-solid fa-pen-to-square"></i> Viết đánh giá
                    </button>
                </div>
            </div>

            <hr class="pd-divider">

            <!-- 2. DANH SÁCH ĐÁNH GIÁ -->
            <div class="review-list">
                <?php if ($total_reviews > 0): ?>
                    <?php $rv_index = 0; // Khởi tạo biến đếm ?>
                    <?php foreach ($reviews as $rv): ?>
                        
                        <div class="review-item rv-item-box" style="<?= $rv_index >= 2 ? 'display: none;' : '' ?>">
                            
                            <!-- Header gộp Avatar và Thông tin -->
                            <div class="rv-user-header">
                                <!-- Tạo Avatar tự động từ tên khách hàng -->
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode(e($rv['customer_name'])) ?>&background=0b2447&color=fff&rounded=true&bold=true" alt="Avatar" class="rv-avatar">
                                
                                <div class="rv-user-info">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <strong><?= e($rv['customer_name']) ?></strong>
                                        <span class="rv-verified"><i class="fa-solid fa-check-circle"></i> Đã xác thực</span>
                                    </div>
                                    <div class="rv-user-stars text-amber">
                                        <?php 
                                            for($i=1; $i<=5; $i++) {
                                                echo $i <= $rv['rating'] ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star" style="color:var(--gray-300)"></i>';
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="rv-content">
                                <?= nl2br(e($rv['content'])) ?>
                            </div>
                            
                            <?php if (!empty($rv['images'])): ?>
                                <div class="rv-images">
                                    <?php 
                                        $rv_images = explode(',', $rv['images']);
                                        foreach($rv_images as $img): 
                                    ?>
                                        <img src="<?= ROOT_URL ?>uploads/products/reviews/<?= trim($img) ?>" alt="Review Image" onclick="window.open(this.src)">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="rv-meta">
                                <span class="rv-date"><?= date('d/m/Y', strtotime($rv['created_at'])) ?></span>
                                <button class="btn-report" onclick="openReportModal(<?= $rv['id'] ?>)"><i class="fa-regular fa-flag"></i> Báo cáo</button>
                            </div>
                        </div>
                        
                        <?php $rv_index++; // Tăng biến đếm ?>
                    <?php endforeach; ?>

                    <!-- Nút Xem thêm hiển thị nếu tổng số đánh giá > 10 -->
                    <?php if ($total_reviews > 2): ?>
                        <div class="text-center" id="loadMoreReviewsBox" style="margin-top: 20px;">
                            <button class="btn btn-outline" style="border-color: var(--gray-300); color: var(--navy); width: 100%; max-width: 250px; padding: 10px; justify-content: center; text-align: center;" onclick="loadMoreReviews()">
                                Xem thêm đánh giá <i class="fa-solid fa-angle-down"></i>
                            </button>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <p style="text-align: center; color: var(--gray-500); padding: 20px 0;">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p>
                <?php endif; ?>
            </div>
        </div>
        <!-- 3. MODAL VIẾT ĐÁNH GIÁ (ẨN MẶC ĐỊNH) -->
        <div id="reviewModal" class="modal-overlay">
            <div class="modal-box rv-modal-box">
                <button class="rv-close-btn" onclick="document.getElementById('reviewModal').classList.remove('active')"><i class="fa-solid fa-xmark"></i></button>
                <h3 class="rv-modal-title">Đánh giá sản phẩm</h3>
                <p class="rv-product-name"><?= e($product['name']) ?></p>

                <!-- Form gửi đánh giá (chú ý action file) -->
                <form id="frmReview" action="actions/add-review.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="rv-rating-select">
                        <label>Đánh giá của bạn về sản phẩm:</label>
                        <div class="star-rating-input" id="starRatingInput">
                            <i class="fa-solid fa-star active" data-val="1"></i>
                            <i class="fa-solid fa-star active" data-val="2"></i>
                            <i class="fa-solid fa-star active" data-val="3"></i>
                            <i class="fa-solid fa-star active" data-val="4"></i>
                            <i class="fa-solid fa-star active" data-val="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" value="5">
                    </div>

                    <div class="rv-form-group">
                        <input type="text" name="customer_name" class="form-control" placeholder="Nhập họ tên của bạn" required>
                    </div>
                    <div class="rv-form-group">
                        <input type="email" name="customer_email" class="form-control" placeholder="Nhập email của bạn" required>
                    </div>
                    <div class="rv-form-group">
                        <textarea name="content" class="form-control" rows="4" placeholder="Nhập nội dung đánh giá của bạn về sản phẩm này..." required></textarea>
                    </div>

                    <div class="rv-upload-wrap">
                        <label for="reviewImages" class="rv-upload-btn">
                            <i class="fa-solid fa-camera"></i> Đính kèm hình ảnh (chọn tối đa 3 hình)
                        </label>
                        <input type="file" id="reviewImages" name="images[]" multiple accept="image/jpeg, image/png, image/webp" style="display:none;" onchange="previewReviewImages()">
                        <div id="rvImagePreview" class="rv-image-preview"></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">Gửi đánh giá</button>
                </form>
            </div>
        </div>

        <div id="reportReviewModal" class="modal-overlay">
            <div class="modal-box rv-modal-box" style="width: 450px;">
                <button class="rv-close-btn" onclick="document.getElementById('reportReviewModal').classList.remove('active')"><i class="fa-solid fa-xmark"></i></button>
                <h3 class="rv-modal-title" style="color: #dc2626; margin-bottom: 20px;"><i class="fa-solid fa-triangle-exclamation"></i> Báo cáo vi phạm</h3>
                
                <form id="frmReportReview">
                    <input type="hidden" id="reportReviewId" name="review_id" value="">
                    
                    <p style="font-weight: 600; margin-bottom: 10px; color: var(--navy);">Vui lòng chọn lý do báo cáo:</p>
                    
                    <div class="report-options">
                        <label class="report-radio"><input type="radio" name="reason" value="Ngôn từ đả kích, phản cảm" required> Ngôn từ đả kích, phản cảm</label>
                        <label class="report-radio"><input type="radio" name="reason" value="Chứa liên kết spam, quảng cáo"> Chứa liên kết spam, quảng cáo</label>
                        <label class="report-radio"><input type="radio" name="reason" value="Đánh giá không đúng sự thật"> Đánh giá không đúng sự thật</label>
                        <label class="report-radio"><input type="radio" name="reason" value="Hình ảnh không liên quan"> Hình ảnh không phù hợp / phản cảm</label>
                    </div>

                    <button type="button" class="btn btn-primary btn-block btn-lg" style="margin-top: 20px; background: #dc2626; border: none;" onclick="submitReport()">Gửi báo cáo</button>
                </form>
            </div>
        </div>

        <script>
            document.getElementById('frmReview').addEventListener('submit', function(e) {
                if (this.checkValidity()) {
                    showLoading('Đang gửi đánh giá...');
                }
            });
            const stars = document.querySelectorAll('#starRatingInput i');
            const ratingInput = document.getElementById('ratingValue');

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    let val = parseInt(this.getAttribute('data-val'));
                    ratingInput.value = val;
                    
                    // Đổi màu các sao
                    stars.forEach(s => {
                        if(parseInt(s.getAttribute('data-val')) <= val) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
            });

            // Xem trước hình ảnh tải lên
            function previewReviewImages() {
                const preview = document.getElementById('rvImagePreview');
                const fileInput = document.getElementById('reviewImages');
                preview.innerHTML = ''; // Xóa preview cũ

                // Giới hạn 3 file
                if(fileInput.files.length > 3) {
                    alert('Bạn chỉ được chọn tối đa 3 hình ảnh!');
                    fileInput.value = ''; // Reset
                    return;
                }

                Array.from(fileInput.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        preview.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                });
            }

            function openReportModal(reviewId) {
                document.getElementById('reportReviewId').value = reviewId;
                document.getElementById('reportReviewModal').classList.add('active');
            }

            function submitReport() {
                const form = document.getElementById('frmReportReview');
                const reason = form.querySelector('input[name="reason"]:checked');
                const reviewId = document.getElementById('reportReviewId').value;

                if (!reason) {
                    alert('Vui lòng chọn một lý do báo cáo!');
                    return;
                }

                const formData = new FormData();
                formData.append('review_id', reviewId);
                formData.append('reason', reason.value);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

                document.getElementById('reportReviewModal').classList.remove('active');
                showToast('Cảm ơn bạn! Báo cáo đã được gửi cho Quản trị viên.', 'success');

                fetch('actions/report-review.php', {
                    method: 'POST',
                    body: formData
                }).catch(err => console.log(err));
            }

            function loadMoreReviews() {

                let hiddenReviews = document.querySelectorAll('.rv-item-box[style*="display: none"]');
                
                // Mỗi lần bấm sẽ hiện thêm 10 đánh giá nữa
                let itemsToShow = 2; 
                
                for (let i = 0; i < hiddenReviews.length; i++) {
                    if (i < itemsToShow) {
    
                        hiddenReviews[i].style.display = 'block';
                    }
                }
                
                if (hiddenReviews.length <= itemsToShow) {
                    document.getElementById('loadMoreReviewsBox').style.display = 'none';
                }
            }
        </script>

        <div class="pd-content-card">
            <?php
            // LỌC 8 SẢN PHẨM CÙNG DANH MỤC
            $relatedCount = 0;
            $relatedProducts = [];
            
            foreach ($allProducts as $p) {
                // Điều kiện: Trạng thái bật (1) + Cùng danh mục + KHÔNG phải sản phẩm đang xem
                if ($p['status'] == 1 && $p['category_id'] == $product['category_id'] && $p['id'] != $product['id']) {
                    $relatedProducts[] = $p;
                    $relatedCount++;
                    if ($relatedCount >= 8) break; // Đủ 8 cái thì dừng
                }
            }
            ?>

            <?php if (count($relatedProducts) > 0): ?>
            <div class="pd-related-section">
                <h3 class="pd-related-title">Sản phẩm cùng danh mục</h3>
                <div class="pd-related-grid">
                    <?php foreach ($relatedProducts as $rp): ?>
                        <div class="rp-card">
                            <div class="rp-img-wrap">
                                <a href="?page=detail_product&slug=<?= $rp['slug'] ?>">
                                    <img src="<?= ROOT_URL ?>uploads/products/images/<?= $rp['image'] ?>"
                                        alt="<?= htmlspecialchars($rp['name']) ?>">
                                </a>
                                <div class="rp-overlay"></div>
                                <?php if ($rp['old_price'] > $rp['price'] && $rp['old_price'] > 0): ?>
                                    <?php $discount = round((($rp['old_price'] - $rp['price']) / $rp['old_price']) * 100); ?>
                                    <span class="rp-badge">-<?= $discount ?>%</span>
                                <?php endif; ?>
                                <a href="?page=detail_product&slug=<?= $rp['slug'] ?>" class="rp-quick-btn">
                                    Xem chi tiết →
                                </a>
                            </div>
                            <div class="rp-body">
                                <a href="?page=detail_product&slug=<?= $rp['slug'] ?>" class="rp-name">
                                    <?= htmlspecialchars($rp['name']) ?>
                                </a>
                                <span class="product-power" style="margin: 0;"><i class="fa-solid fa-bolt product-power-icon"></i> <?php echo $rp['power_capacity'] ?></span>
                                <div class="product-price-row" style="margin-bottom: 5px;">
                                    <div>
                                        <?php if($rp['price'] > 0){ ?>
                                            <div class="product-old-price"><?= number_format($rp['old_price'], 0, ',', '.') ?>đ</div>
                                            <div class="product-price"><?= number_format($rp['price'], 0, ',', '.') ?>đ</div>
                                        <?php } else { ?>
                                            <span class="rp-price-contact">Liên hệ báo giá</span>
                                        <?php } ?>
                                    </div>
                                </div>

                                <div class="product-card-rating" style="margin-bottom: 5px;">
                                    <div class="stars-gold">
                                        <?php 
                                            $revStats  = $reviewObj->getReviewsAndStatsByProduct($rp['id']);
                                            $avgRating = (float)($revStats['avg_rating'] ?? 0);
                                            $totalRev  = (int)($revStats['total'] ?? 0);

                                            for($i=1; $i<=5; $i++) {
                                                if($i <= round($avgRating)) {
                                                    echo '<i class="fa-solid fa-star"></i>';
                                                } else {
                                                    echo '<i class="fa-regular fa-star"></i>';
                                                }
                                            }
                                        ?>
                                    </div>
                                    <span class="rating-count">
                                        <?= $avgRating > 0 ? number_format($avgRating, 1) : '5.0' ?> (<?= $totalRev ?>)
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
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