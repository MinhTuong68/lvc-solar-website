<?php
    // DEMO DATA
    // Nếu bạn đã có dữ liệu từ DB rồi thì giữ phần query của bạn,
    // chỉ thay phần HTML + JS bên dưới là được.
    $sp = [
        'id' => 101,
        'name' => 'Tấm pin Canadian Solar 450W',
        'slug' => 'tam-pin-canadian-solar-450w',
        'price' => 2500000,
        'old_price' => 2800000,
        'power_capacity' => '450W',
        'warranty' => '12 năm',
        'stock' => 50,
        'status' => 1,
        'category_id' => 2,
        'brand_id' => 5,
        'image' => 'canadian_450w.png',
        'video' => 'gioi_thieu_canadian.mp4',
        'description' => 'Tấm pin mặt trời hiệu suất cao, phù hợp cho hệ thống dân dụng và thương mại.'
    ];

    $galleries = [
        ['id' => 1, 'image_path' => 'gallery_1.png'],
        ['id' => 2, 'image_path' => 'gallery_2.png'],
        ['id' => 3, 'image_path' => 'gallery_3.png']
    ];

    $specs = [
        ['name' => 'Hiệu suất', 'value' => '21.3%'],
        ['name' => 'Trọng lượng', 'value' => '24.5 kg']
    ];
?>

<link rel="stylesheet" href="assets/css/edit-product-pro.css">

<div class="lvc-ep-page">
    <div class="lvc-ep-container">

        <div class="lvc-ep-topbar">
            <div class="lvc-ep-topbar-left">
                <div class="lvc-ep-breadcrumb">
                    <a href="index.php?page=manage-products">Sản phẩm</a>
                    <span>/</span>
                    <span>Chỉnh sửa sản phẩm</span>
                </div>

                <h1 class="lvc-ep-page-title">Cập nhật sản phẩm</h1>

                <div class="lvc-ep-page-meta">
                    <span class="lvc-ep-chip lvc-ep-chip-neutral">Mã ID: #<?= (int)$sp['id'] ?></span>

                    <?php if ((int)$sp['status'] === 1): ?>
                        <span class="lvc-ep-chip lvc-ep-chip-success">Đang bán</span>
                    <?php else: ?>
                        <span class="lvc-ep-chip lvc-ep-chip-muted">Ngừng kinh doanh</span>
                    <?php endif; ?>

                    <span class="lvc-ep-chip lvc-ep-chip-soft">
                        Tồn kho: <?= (int)$sp['stock'] ?>
                    </span>
                </div>
            </div>

            <div class="lvc-ep-topbar-right">
                <a href="index.php?page=manage-products" class="lvc-ep-btn lvc-ep-btn-light">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Quay lại</span>
                </a>

                <button type="button" class="lvc-ep-btn lvc-ep-btn-primary" onclick="document.getElementById('editProductForm').submit();">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu thay đổi</span>
                </button>
            </div>
        </div>

        <form id="editProductForm" action="actions/edit-product-process.php" method="POST" enctype="multipart/form-data" class="lvc-ep-layout">
            <input type="hidden" name="id" value="<?= (int)$sp['id'] ?>">

            <div class="lvc-ep-main">

                <!-- THÔNG TIN CƠ BẢN -->
                <section class="lvc-ep-card">
                    <div class="lvc-ep-card-head">
                        <div>
                            <h2 class="lvc-ep-card-title">
                                <i class="fa-solid fa-circle-info"></i>
                                Thông tin cơ bản
                            </h2>
                            <p class="lvc-ep-card-desc">Thông tin hiển thị chính của sản phẩm trên hệ thống.</p>
                        </div>
                    </div>

                    <div class="lvc-ep-field">
                        <label for="lvc_ep_name">Tên sản phẩm <span class="lvc-ep-required">*</span></label>
                        <input
                            id="lvc_ep_name"
                            type="text"
                            name="name"
                            class="lvc-ep-input"
                            value="<?= htmlspecialchars($sp['name']) ?>"
                            placeholder="Nhập tên sản phẩm"
                            required
                        >
                    </div>

                    <div class="lvc-ep-field">
                        <label for="lvc_ep_slug">Đường dẫn SEO (slug) <span class="lvc-ep-required">*</span></label>
                        <input
                            id="lvc_ep_slug"
                            type="text"
                            name="slug"
                            class="lvc-ep-input"
                            value="<?= htmlspecialchars($sp['slug']) ?>"
                            placeholder="vi-du-tam-pin-canadian-solar-450w"
                            required
                        >
                        <small class="lvc-ep-note">Nên dùng chữ thường, không dấu và dấu gạch ngang.</small>
                    </div>

                    <div class="lvc-ep-field">
                        <label for="lvc_ep_desc">Mô tả chi tiết sản phẩm</label>
                        <textarea
                            id="lvc_ep_desc"
                            name="description"
                            class="lvc-ep-textarea"
                            rows="6"
                            placeholder="Nhập mô tả sản phẩm"
                        ><?= htmlspecialchars($sp['description']) ?></textarea>
                    </div>
                </section>

                <!-- ẢNH ĐẠI DIỆN & VIDEO -->
                <section class="lvc-ep-card">
                    <div class="lvc-ep-card-head">
                        <div>
                            <h2 class="lvc-ep-card-title">
                                <i class="fa-solid fa-photo-film"></i>
                                Ảnh đại diện & video
                            </h2>
                            <p class="lvc-ep-card-desc">Giữ nguyên nếu bạn không tải file mới lên.</p>
                        </div>
                    </div>

                    <div class="lvc-ep-media-grid">
                        <div class="lvc-ep-media-box">
                            <div class="lvc-ep-media-head">
                                <h3>Ảnh đại diện chính</h3>
                                <span class="lvc-ep-media-badge">Thumbnail</span>
                            </div>

                            <div class="lvc-ep-media-preview">
                                <img
                                    src="../uploads/products/images/<?= htmlspecialchars($sp['image']) ?>"
                                    alt="Ảnh sản phẩm"
                                    onerror="this.src='../assets/images/no-image.png'"
                                >
                            </div>

                            <input type="hidden" name="old_image" value="<?= htmlspecialchars($sp['image']) ?>">

                            <div class="lvc-ep-field lvc-ep-field-no-margin">
                                <input type="file" name="image" class="lvc-ep-file" accept="image/*">
                                <small class="lvc-ep-note">Định dạng khuyên dùng: JPG, PNG, WEBP.</small>
                            </div>
                        </div>

                        <div class="lvc-ep-media-box">
                            <div class="lvc-ep-media-head">
                                <h3>Video sản phẩm</h3>
                                <span class="lvc-ep-media-badge">Không bắt buộc</span>
                            </div>

                            <div class="lvc-ep-media-preview lvc-ep-media-preview-video">
                                <?php if (!empty($sp['video'])): ?>
                                    <i class="fa-solid fa-circle-play"></i>
                                    <div class="lvc-ep-video-text">
                                        <strong>Đã có video</strong>
                                        <span><?= htmlspecialchars($sp['video']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <i class="fa-solid fa-video-slash"></i>
                                    <div class="lvc-ep-video-text">
                                        <strong>Chưa có video</strong>
                                        <span>Bạn có thể tải lên file mới</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <input type="hidden" name="old_video" value="<?= htmlspecialchars($sp['video']) ?>">

                            <div class="lvc-ep-field lvc-ep-field-no-margin">
                                <input type="file" name="video" class="lvc-ep-file" accept="video/mp4,video/x-m4v,video/*">
                                <small class="lvc-ep-note">Có thể bỏ trống nếu giữ nguyên video cũ.</small>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- GALLERY -->
                <section class="lvc-ep-card">
                    <div class="lvc-ep-card-head">
                        <div>
                            <h2 class="lvc-ep-card-title">
                                <i class="fa-regular fa-images"></i>
                                Thư viện ảnh
                            </h2>
                            <p class="lvc-ep-card-desc">Ảnh đang có sẽ hiển thị bên dưới. Bấm X để đánh dấu xóa.</p>
                        </div>
                    </div>

                    <div class="lvc-ep-gallery-toolbar">
                        <div class="lvc-ep-gallery-counter">
                            Tổng ảnh hiện tại:
                            <strong><?= count($galleries) ?></strong>
                        </div>
                    </div>

                    <div class="lvc-ep-gallery-grid" id="old-gallery-container">
                        <?php if (!empty($galleries)): ?>
                            <?php foreach ($galleries as $gal): ?>
                                <div class="lvc-ep-gallery-item" id="gal_<?= (int)$gal['id'] ?>">
                                    <div class="lvc-ep-gallery-thumb">
                                        <img
                                            src="../uploads/products/image_gallery/<?= htmlspecialchars($gal['image_path']) ?>"
                                            alt="Gallery image"
                                            onerror="this.src='../assets/images/no-image.png'"
                                        >
                                    </div>

                                    <button
                                        type="button"
                                        class="lvc-ep-gallery-remove"
                                        onclick="removeOldGallery(<?= (int)$gal['id'] ?>)"
                                        title="Xóa ảnh này"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>

                                    <div class="lvc-ep-gallery-meta">
                                        <span>Ảnh #<?= (int)$gal['id'] ?></span>
                                    </div>

                                    <input type="hidden" name="gallery_sort_order[]" value="<?= (int)$gal['id'] ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="lvc-ep-empty-box">
                                <i class="fa-regular fa-image"></i>
                                <span>Hiện chưa có ảnh phụ nào.</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="lvc-ep-divider"></div>

                    <div class="lvc-ep-field lvc-ep-field-no-margin">
                        <label for="lvc_ep_new_gallery">Thêm ảnh phụ mới</label>
                        <input id="lvc_ep_new_gallery" type="file" name="new_gallery[]" class="lvc-ep-file" accept="image/*" multiple>
                        <small class="lvc-ep-note">Có thể chọn nhiều ảnh cùng lúc.</small>
                    </div>
                </section>

                <!-- THÔNG SỐ -->
                <section class="lvc-ep-card">
                    <div class="lvc-ep-card-head">
                        <div>
                            <h2 class="lvc-ep-card-title">
                                <i class="fa-solid fa-list-check"></i>
                                Thông số kỹ thuật
                            </h2>
                            <p class="lvc-ep-card-desc">Bổ sung các thông số chi tiết để người quản trị dễ kiểm soát.</p>
                        </div>
                    </div>

                    <div id="lvcEpSpecsContainer" class="lvc-ep-specs-wrap">
                        <?php if (!empty($specs)): ?>
                            <?php foreach ($specs as $spec): ?>
                                <div class="lvc-ep-spec-row">
                                    <input
                                        type="text"
                                        name="spec_names[]"
                                        class="lvc-ep-input"
                                        value="<?= htmlspecialchars($spec['name']) ?>"
                                        placeholder="Tên thông số"
                                    >
                                    <input
                                        type="text"
                                        name="spec_values[]"
                                        class="lvc-ep-input"
                                        value="<?= htmlspecialchars($spec['value']) ?>"
                                        placeholder="Giá trị"
                                    >
                                    <button type="button" class="lvc-ep-spec-remove" onclick="this.parentElement.remove()" title="Xóa dòng này">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="lvc-ep-add-row-btn" onclick="addSpecRow()">
                        <i class="fa-solid fa-plus"></i>
                        <span>Thêm thông số</span>
                    </button>
                </section>
            </div>

            <aside class="lvc-ep-sidebar">

                <section class="lvc-ep-card lvc-ep-card-sticky">
                    <div class="lvc-ep-card-head">
                        <div>
                            <h2 class="lvc-ep-card-title">
                                <i class="fa-solid fa-sliders"></i>
                                Cài đặt kinh doanh
                            </h2>
                            <p class="lvc-ep-card-desc">Các thông tin vận hành chính của sản phẩm.</p>
                        </div>
                    </div>

                    <div class="lvc-ep-field">
                        <label for="lvc_ep_status">Trạng thái</label>
                        <select id="lvc_ep_status" name="status" class="lvc-ep-select">
                            <option value="1" <?= ((int)$sp['status'] === 1) ? 'selected' : '' ?>>Đang bán</option>
                            <option value="0" <?= ((int)$sp['status'] === 0) ? 'selected' : '' ?>>Ngừng kinh doanh</option>
                        </select>
                    </div>

                    <div class="lvc-ep-field">
                        <label for="lvc_ep_stock">Tồn kho</label>
                        <input
                            id="lvc_ep_stock"
                            type="number"
                            name="stock"
                            class="lvc-ep-input"
                            value="<?= (int)$sp['stock'] ?>"
                            placeholder="Nhập số lượng tồn kho"
                        >
                    </div>
                </section>

                <section class="lvc-ep-card">
                    <div class="lvc-ep-card-head">
                        <div>
                            <h2 class="lvc-ep-card-title">
                                <i class="fa-solid fa-sitemap"></i>
                                Phân loại
                            </h2>
                            <p class="lvc-ep-card-desc">Chọn danh mục và thương hiệu phù hợp.</p>
                        </div>
                    </div>

                    <div class="lvc-ep-field">
                        <label for="lvc_ep_category">Danh mục</label>
                        <select id="lvc_ep_category" name="category_id" class="lvc-ep-select">
                            <option value="1" <?= ((int)$sp['category_id'] === 1) ? 'selected' : '' ?>>Biến tần (Inverter)</option>
                            <option value="2" <?= ((int)$sp['category_id'] === 2) ? 'selected' : '' ?>>Tấm pin mặt trời</option>
                        </select>
                    </div>

                    <div class="lvc-ep-field lvc-ep-field-no-margin">
                        <label for="lvc_ep_brand">Thương hiệu</label>
                        <select id="lvc_ep_brand" name="brand_id" class="lvc-ep-select">
                            <option value="5" <?= ((int)$sp['brand_id'] === 5) ? 'selected' : '' ?>>Canadian Solar</option>
                        </select>
                    </div>
                </section>

                <section class="lvc-ep-card">
                    <div class="lvc-ep-card-head">
                        <div>
                            <h2 class="lvc-ep-card-title">
                                <i class="fa-solid fa-tags"></i>
                                Giá bán
                            </h2>
                            <p class="lvc-ep-card-desc">Thiết lập giá chính và giá so sánh.</p>
                        </div>
                    </div>

                    <div class="lvc-ep-field">
                        <label for="lvc_ep_price">Giá hiện tại (VNĐ)</label>
                        <input
                            id="lvc_ep_price"
                            type="number"
                            name="price"
                            class="lvc-ep-input lvc-ep-input-price"
                            value="<?= (int)$sp['price'] ?>"
                            placeholder="Nhập giá bán"
                        >
                    </div>

                    <div class="lvc-ep-field lvc-ep-field-no-margin">
                        <label for="lvc_ep_old_price">Giá cũ (VNĐ)</label>
                        <input
                            id="lvc_ep_old_price"
                            type="number"
                            name="old_price"
                            class="lvc-ep-input"
                            value="<?= (int)$sp['old_price'] ?>"
                            placeholder="Nhập giá cũ"
                        >
                    </div>
                </section>

                <section class="lvc-ep-card">
                    <div class="lvc-ep-card-head">
                        <div>
                            <h2 class="lvc-ep-card-title">
                                <i class="fa-solid fa-bolt"></i>
                                Nhãn nổi bật
                            </h2>
                            <p class="lvc-ep-card-desc">Thông tin ngắn gọn thường hiển thị ở card/list sản phẩm.</p>
                        </div>
                    </div>

                    <div class="lvc-ep-field">
                        <label for="lvc_ep_power">Công suất</label>
                        <input
                            id="lvc_ep_power"
                            type="text"
                            name="power_capacity"
                            class="lvc-ep-input"
                            value="<?= htmlspecialchars($sp['power_capacity']) ?>"
                            placeholder="Ví dụ: 450W"
                        >
                    </div>

                    <div class="lvc-ep-field lvc-ep-field-no-margin">
                        <label for="lvc_ep_warranty">Bảo hành</label>
                        <input
                            id="lvc_ep_warranty"
                            type="text"
                            name="warranty"
                            class="lvc-ep-input"
                            value="<?= htmlspecialchars($sp['warranty']) ?>"
                            placeholder="Ví dụ: 12 năm"
                        >
                    </div>
                </section>

            </aside>

            <div id="deleted-gallery-inputs"></div>
        </form>
    </div>
</div>

<script>
    function addSpecRow() {
        const container = document.getElementById('lvcEpSpecsContainer');
        const row = document.createElement('div');
        row.className = 'lvc-ep-spec-row';
        row.innerHTML = `
            <input type="text" name="spec_names[]" class="lvc-ep-input" placeholder="Tên thông số">
            <input type="text" name="spec_values[]" class="lvc-ep-input" placeholder="Giá trị">
            <button type="button" class="lvc-ep-spec-remove" onclick="this.parentElement.remove()" title="Xóa dòng này">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        container.appendChild(row);
    }

    function removeOldGallery(galleryId) {
        const item = document.getElementById('gal_' + galleryId);
        if (!item) return;

        item.style.display = 'none';

        const sortInput = item.querySelector('input[name="gallery_sort_order[]"]');
        if (sortInput) {
            sortInput.disabled = true;
        }

        const existed = document.querySelector('#deleted-gallery-inputs input[value="' + galleryId + '"]');
        if (!existed) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_gallery_ids[]';
            input.value = galleryId;
            document.getElementById('deleted-gallery-inputs').appendChild(input);
        }
    }
</script>