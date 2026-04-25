<?php
    if (!isset($_GET['id'])){
        header("location: index.php?page=manage-products");
        exit();
    }
    $id = (int)$_GET['id'];
    $query_sp = mysqli_query($conn, "SELECT * FROM tbl_products WHERE id = $id");
    $sp = mysqli_fetch_assoc($query_sp);

    if (!$sp) {
        $_SESSION['toast_message'] = "Sản phẩm không tồn tại";
        $_SESSION['toast_type'] = 'error';    
        header("location: index.php?page=manage-products");
        exit();
    }

    $query_gal = mysqli_query($conn, "SELECT * FROM tbl_product_gallery WHERE product_id = $id ORDER BY sort_order ASC");
    $galleries = [];
    if ($query_gal && mysqli_num_rows($query_gal) > 0) {
        while ($row = mysqli_fetch_assoc($query_gal)) {
            $galleries[] = $row;
        }
    }

    $query_categories = mysqli_query($conn, "SELECT * FROM tbl_categories ORDER BY id DESC");

    $query_brands = mysqli_query($conn, "SELECT * FROM tbl_brands ORDER BY id DESC");
?>
<link rel="stylesheet" href="assets/css/edit.css">
<div class="form-page">
    <div class="form-contain">
        <div class="form-topbar">
            <div class="form-topbar-left">
                <div class="form-breadcrumb">
                    <a href="index.php?page=manage-products">Sản phẩm</a>
                    <span>/</span>
                    <span>Chỉnh sửa sản phẩm</span>
                </div>

                <h1 class="form-page-title">Cập nhật sản phẩm</h1>

                <div class="form-page-meta">
                    <span class="form-chip form-chip-neutral">Mã ID: #</span>

                   
                        <span class="form-chip form-chip-success">Đang bán</span>
                    
                        <span class="form-chip form-chip-muted">Ngừng kinh doanh</span>
                  

                    <span class="form-chip form-chip-soft">
                        Tồn kho: 
                    </span>
                </div>
            </div>

            <div class="form-topbar-right">
                <a href="index.php?page=manage-products" class="form-btn form-btn-light">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Quay lại</span>
                </a>

                <button type="submit" form="editProductForm" name="btn_edit_product" class="form-btn form-btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu thay đổi</span>
                </button>
            </div>
        </div>

        <form id="editProductForm" action="actions/edit-product.php" method="POST" enctype="multipart/form-data" class="form-layout">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="form-main">
                
                <!-- THÔNG TIN CƠ BẢN -->
                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-solid fa-circle-info"></i>
                                Thông tin cơ bản
                            </h2>
                            <p class="form-card-desc">Thông tin hiển thị chính của sản phẩm trên hệ thống.</p>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="edit_product_name">Tên sản phẩm <span class="form-required">*</span></label>
                        <input
                            id="edit_product_name"
                            type="text"
                            name="name"
                            class="form-input"
                            value="<?php echo $sp['name'] ?>"
                            placeholder="Nhập tên sản phẩm"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="edit_product_slug">Đường dẫn SEO (slug) <span class="form-required">*</span></label>
                        <input
                            id="edit_product_slug"
                            type="text"
                            name="slug"
                            class="form-input"
                            value="<?php echo $sp['slug'] ?>"
                            placeholder="vi-du-tam-pin-canadian-solar-450w"
                            required
                        >
                        <small class="form-note">Nên dùng chữ thường, không dấu và dấu gạch ngang.</small>
                    </div>

                    <div class="form-field">
                        <label for="form_desc">Mô tả chi tiết sản phẩm</label>
                        <textarea
                            id="form_desc"
                            name="short_description"
                            class="form-textarea"
                            rows="6"
                            placeholder="Nhập mô tả sản phẩm"   
                        ><?php echo htmlspecialchars($sp['short_description']) ?></textarea>
                    </div>
                </section>

                <!-- ẢNH ĐẠI DIỆN & VIDEO -->
                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-solid fa-photo-film"></i>
                                Ảnh đại diện & video
                            </h2>
                            <p class="form-card-desc">Giữ nguyên nếu bạn không tải file mới lên.</p>
                        </div>
                    </div>

                    <div class="form-media-grid">
                        <div class="form-media-box">
                            <div class="form-media-head">
                                <h3>Ảnh đại diện chính</h3>
                                <span class="form-media-badge">Thumbnail</span>
                            </div>

                            <div class="form-media-preview">
                                <img style="border-radius: 10px;"
                                    src="<?php echo ROOT_URL ?>/uploads/products/images/<?php echo $sp['image'] ?>"
                                    alt="Ảnh sản phẩm"
                                >
                            </div><br>

                            <input type="hidden" name="old_image" value="<?php echo $sp['image']; ?>">

                            <div class="form-field form-field-no-margin">
                                <input type="file" name="image" class="form-file" accept="image/*">
                                <small class="form-note">Định dạng khuyên dùng: JPG, PNG, WEBP.</small>
                            </div>
                        </div>

                        <div class="form-media-box">
                            <div class="form-media-head">
                                <h3>Video sản phẩm</h3>
                                <span class="form-media-badge">Không bắt buộc</span>
                            </div>

                            <div class="form-media-preview form-media-preview-video">
                                <?php
                                    if(!empty($sp['video'])){
                                        ?>
                                            <div class="form-video-text">
                                                <video controls class ="form-video">
                                                    <source src="<?php echo ROOT_URL ?>/uploads/products/videos/<?php echo $sp['video'] ?>" type="video/mp4">
                                                    Trình duyệt của bạn không hỗ trợ xem video.
                                                </video>                
                                            </div>
                                        <?php
                                    }
                                    else{ 
                                        ?>
                                            <i class="fa-solid fa-video-slash"></i>
                                            <div class="form-video-text">
                                                <strong>Chưa có video</strong>
                                                <span>Bạn có thể tải lên file mới</span>
                                            </div>
                                        <?php
                                    }
                                ?>
                            </div><br>

                            <input type="hidden" name="old_video" value="<?php echo $sp['video'] ?>">

                            <div class="form-field form-field-no-margin">
                                <input type="file" name="video" class="form-file" accept="video/mp4,video/x-m4v,video/*">
                                <small class="form-note">Có thể bỏ trống nếu giữ nguyên video cũ.</small>
                            </div>
                        </div>
                    </div>
                </section> 

                <section class="form-card">
                    <div class="form-field">
                        <label for="form_desc">Nội dung chi tiết sản phẩm</label>
                        <textarea id="form_content"
                            name="content"
                            class="form-textarea"
                            rows="12"  
                        ><?php echo htmlspecialchars($sp['content']) ?></textarea>
                    </div>
                </section>

                <!-- GALLERY -->
                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-regular fa-images"></i>
                                Thư viện ảnh
                            </h2>
                            <p class="form-card-desc">Ảnh đang có sẽ hiển thị bên dưới. Bấm dấu X để xóa ảnh. Có thể kéo ảnh để thay đổi vị trí hiễn thị</p>
                        </div>
                    </div>

                    <div class="form-gallery-toolbar">
                        <div class="form-gallery-counter">
                            Tổng ảnh hiện tại:
                            <strong><?= count($galleries) ?></strong>
                        </div>
                    </div>

                    <div class="form-gallery-grid" id="old-gallery-container">
                        <?php if (!empty($galleries)): ?>
                            <?php
                                $stt=1;
                                foreach ($galleries as $gal): 
                                    ?>
                                        <div class="form-gallery-item" id="gal_<?= (int)$gal['id'] ?>" draggable="true">
                                            <div class="form-gallery-thumb">
                                                <img
                                                    src="../uploads/products/image_gallery/<?= htmlspecialchars($gal['image_path']) ?>"
                                                    alt="Gallery image"
                                                    onerror="this.src='../assets/images/no-image.png'"
                                                >
                                            </div>

                                            <button
                                                type="button"
                                                class="form-gallery-remove"
                                                onclick="removeOldGallery(<?= (int)$gal['id'] ?>)"
                                                
                                                title="Xóa ảnh này"
                                            >
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>

                                            <div class="form-gallery-meta">
                                                <span>Ảnh #<?= $stt++ ?></span>
                                            </div>

                                            <input type="hidden" name="gallery_sort_order[]" value="<?= (int)$gal['id'] ?>">
                                        </div>
                                    <?php
                                endforeach; 
                            ?>
                        <?php else: ?>
                            <div class="form-empty-box">
                                <i class="fa-regular fa-image"></i>
                                <span>Hiện chưa có ảnh phụ nào.</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-divider"></div>

                    <div class="form-field form-field-no-margin">
                        <label for="edit_product_new_gallery">Thêm ảnh phụ mới</label>
                        <input id="edit_product_new_gallery" type="file" name="new_gallery[]" class="form-file" accept="image/*" multiple>
                        <small class="form-note">Có thể chọn nhiều ảnh cùng lúc.</small>
                    </div>
                </section>

                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-solid fa-list-check"></i>
                                Thông số kỹ thuật
                            </h2>
                            <p class="form-card-desc">Bổ sung các thông số chi tiết để người quản trị dễ kiểm soát.</p>
                        </div>
                    </div>

                    <div id="lvcEpSpecsContainer" class="form-specs-wrap">
                        <?php if (!empty($specs)): ?>
                            <?php foreach ($specs as $spec): ?>
                                <div class="form-spec-row">
                                    <input
                                        type="text"
                                        name="spec_names[]"
                                        class="form-input"
                                        value="<?= htmlspecialchars($spec['name']) ?>"
                                        placeholder="Tên thông số"
                                    >
                                    <input
                                        type="text"
                                        name="spec_values[]"
                                        class="form-input"
                                        value="<?= htmlspecialchars($spec['value']) ?>"
                                        placeholder="Giá trị"
                                    >
                                    <button type="button" class="form-spec-remove" onclick="this.parentElement.remove()" title="Xóa dòng này">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="form-add-row-btn" onclick="addSpecRow()">
                        <i class="fa-solid fa-plus"></i>
                        <span>Thêm thông số</span>
                    </button>
                </section>
            </div>

            <aside class="form-sidebar">
                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-solid fa-sliders"></i>
                                Cài đặt kinh doanh
                            </h2>
                            <p class="form-card-desc">Các thông tin vận hành chính của sản phẩm.</p>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="form_status">Trạng thái</label>
                        <select id="form_status" name="status" class="form-select">
                            <option value="1" <?= ((int)$sp['status'] === 1) ? 'selected' : '' ?>>Đang bán</option>
                            <option value="0" <?= ((int)$sp['status'] === 0) ? 'selected' : '' ?>>Ngừng kinh doanh</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="form_stock">Tồn kho</label>
                        <input
                            id="form_stock"
                            type="number"
                            name="stock"
                            class="form-input"
                            value="<?= (int)$sp['stock'] ?>"
                            placeholder="Nhập số lượng tồn kho"
                        >
                    </div>
                </section>

                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-solid fa-sitemap"></i>
                                Phân loại
                            </h2>
                            <p class="form-card-desc">Chọn danh mục và thương hiệu phù hợp.</p>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="form_category">Danh mục</label>
                        <select id="form_category" name="category_id" class="form-select">
                            <?php
                                if ($query_categories && mysqli_num_rows($query_categories) > 0) {

                                    while ($cat = mysqli_fetch_assoc($query_categories)) {

                                        if ($cat['id'] == $sp['category_id']) {
                                            ?>
                                                <option value="<?php echo $cat['id'] ?>" selected>
                                                    <?php echo $cat['name'] ?>
                                                </option>
                                            <?php
                                        } else {
                                            ?>
                                                <option value="<?php echo $cat['id'] ?>">
                                                    <?php echo $cat['name'] ?>
                                                </option>
                                            <?php
                                        }

                                    }

                                }
                            ?>
                        </select>
                    </div>

                    <div class="form-field form-field-no-margin">
                        <label for="form_brand">Thương hiệu</label>
                        <select id="form_brand" name="brand_id" class="form-select">
                            <?php
                                if ($query_brands && mysqli_num_rows($query_brands) > 0) {

                                    while ($brand = mysqli_fetch_assoc($query_brands)) {

                                        if ($brand['id'] == $sp['brand_id']) {
                                            ?>
                                                <option value="<?php echo $brand['id'] ?>" selected>
                                                    <?php echo $brand['name'] ?>
                                                </option>
                                            <?php
                                        } else {
                                            ?>
                                                <option value="<?php echo $brand['id'] ?>">
                                                    <?php echo $brand['name'] ?>
                                                </option>
                                            <?php
                                        }

                                    }

                                }
                            ?>
                        </select>
                    </div>
                </section>

                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-solid fa-tags"></i>
                                Giá bán
                            </h2>
                            <p class="form-card-desc">Thiết lập giá chính và giá so sánh.</p>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="edit_product_price">Giá hiện tại (VNĐ)</label>
                        <input
                            id="edit_product_price"
                            type="number"
                            name="price"
                            class="form-input form-input-price"
                            value="<?php echo (int)$sp['price'] ?>"
                            placeholder="Nhập giá bán"
                        >
                    </div>

                    <div class="form-field form-field-no-margin">
                        <label for="edit_product_old_price">Giá cũ (VNĐ)</label>
                        <input
                            id="edit_product_old_price"
                            type="number"
                            name="old_price"
                            class="form-input"
                            value="<?php echo (int)$sp['old_price'] ?>"
                            placeholder="Nhập giá cũ"
                        >
                    </div>
                </section>

                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-solid fa-bolt"></i>
                                Thông số
                            </h2>
                            <p class="form-card-desc">Thông tin ngắn gọn về thông số của sản phẩm.</p>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="edit_product_power">Công suất</label>
                        <input
                            id="edit_product_power"
                            type="text"
                            name="power_capacity"
                            class="form-input"
                            value="<?php echo htmlspecialchars($sp['power_capacity']) ?>"
                            placeholder="Ví dụ: 450W"
                        >
                    </div>

                    <div class="form-field form-field-no-margin">
                        <label for="edit_product_warranty">Bảo hành</label>
                        <input
                            id="edit_product_warranty"
                            type="text"
                            name="warranty"
                            class="form-input"
                            value="<?php echo htmlspecialchars($sp['warranty']) ?>"
                            placeholder="Ví dụ: 10 năm"
                        >
                    </div>
                </section>
            </aside>
            <div id="deleted-gallery-inputs"></div>
        </form>
    </div>
</div>
<script src="assets/js/delete.js"></script>
<script src="assets/js/products.js"></script>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('content',{
        filebrowserUploadUrl: '<?php echo ROOT_URL; ?>admin/actions/upload-ckeditor.php',
        filebrowserUploadMethod: 'xhr',
        uploadUrl: '/webctylvc/admin/actions/upload-ckeditor.php'
    }); 
</script>