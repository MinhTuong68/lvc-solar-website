<?php
    $category = [
        'id' => 7,
        'name' => 'Tấm pin mặt trời',
        'slug' => 'tam-pin-mat-troi',
        'status' => 1,
        'created_at' => '2026-04-05 10:30:00'
    ];
?>

<link rel="stylesheet" href="assets/css/edit.css">

<div class="form-page">
    <div class="form-container">

        <div class="form-topbar">
            <div class="form-topbar-left">
                <div class="form-breadcrumb">
                    <a href="index.php?page=manage-category">Danh mục</a>
                    <span>/</span>
                    <span>Chỉnh sửa danh mục</span>
                </div>

                <h1 class="form-page-title">Cập nhật danh mục</h1>

                <div class="form-page-meta">
                    <span class="form-chip form-chip-neutral">ID: #<?= (int)$category['id'] ?></span>

                    <?php if ((int)$category['status'] === 1): ?>
                        <span class="form-chip form-chip-success">Đang hoạt động</span>
                    <?php else: ?>
                        <span class="form-chip form-chip-muted">Tạm ẩn</span>
                    <?php endif; ?>

                    <span class="form-chip form-chip-soft">
                        Tạo lúc: <?= htmlspecialchars($category['created_at']) ?>
                    </span>
                </div>
            </div>

            <div class="form-topbar-right">
                <a href="index.php?page=manage-category" class="form-btn form-btn-light">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Quay lại danh sách</span>
                </a>

                <button
                    type="button"
                    class="form-btn form-btn-primary"
                    onclick="document.getElementById('editCategoryForm').submit();"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu thay đổi</span>
                </button>
            </div>
        </div>

        <form id="editCategoryForm" action="" method="POST" class="form-layout">
            <!-- TODO BACKEND: đổi action nếu cần -->
            <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">

            <div class="form-main">

                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-solid fa-layer-group"></i>
                                Thông tin danh mục
                            </h2>
                            <p class="form-card-desc">
                                Cập nhật thông tin hiển thị và đường dẫn SEO cho danh mục sản phẩm.
                            </p>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="category_name_edit">
                            Tên danh mục <span class="form-required">*</span>
                        </label>
                        <input
                            id="category_name_edit"
                            type="text"
                            name="category_name"
                            class="form-input"
                            value="<?= htmlspecialchars($category['name']) ?>"
                            placeholder="Ví dụ: Tấm pin mặt trời"
                            required
                        >
                        <small class="form-note">
                            Tên này sẽ hiển thị ngoài giao diện website và trong khu vực quản trị.
                        </small>
                    </div>

                    <div class="form-field">
                        <label for="category_slug_edit">
                            Đường dẫn (slug) <span class="form-required">*</span>
                        </label>
                        <input
                            id="category_slug_edit"
                            type="text"
                            name="category_slug"
                            class="form-input"
                            value="<?= htmlspecialchars($category['slug']) ?>"
                            placeholder="vi-du-tam-pin-mat-troi"
                            required
                        >
                        <small class="form-note">
                            Nên dùng chữ thường, không dấu, không khoảng trắng và ngăn cách bằng dấu gạch ngang.
                        </small>
                    </div>

                    <div class="form-field form-field-no-margin">
                        <label for="category_status_edit">Trạng thái</label>
                        <select id="category_status_edit" name="status" class="form-select">
                            <option value="1" <?= ((int)$category['status'] === 1) ? 'selected' : '' ?>>
                                Đang hoạt động
                            </option>
                            <option value="0" <?= ((int)$category['status'] === 0) ? 'selected' : '' ?>>
                                Tạm ẩn
                            </option>
                        </select>
                    </div>
                </section>

                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-solid fa-link"></i>
                                Xem trước hiển thị
                            </h2>
                            <p class="form-card-desc">
                                Khối này giúp người nhập liệu hình dung cách danh mục xuất hiện trên hệ thống.
                            </p>
                        </div>
                    </div>

                    <div class="category-preview-box">
                        <div class="category-preview-label">Tên danh mục</div>
                        <div class="category-preview-name" id="previewCategoryName">
                            <?= htmlspecialchars($category['name']) ?>
                        </div>

                        <div class="category-preview-label" style="margin-top: 16px;">Đường dẫn</div>
                        <div class="category-preview-slug">
                            /danh-muc/<span id="previewCategorySlug"><?= htmlspecialchars($category['slug']) ?></span>
                        </div>

                        <div class="category-preview-status">
                            <span class="category-status-dot <?= ((int)$category['status'] === 1) ? 'is-active' : 'is-hidden' ?>"></span>
                            <span id="previewCategoryStatus">
                                <?= ((int)$category['status'] === 1) ? 'Đang hoạt động' : 'Tạm ẩn' ?>
                            </span>
                        </div>
                    </div>
                </section>

            </div>

            <aside class="form-sidebar">

                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-solid fa-shield-halved"></i>
                                Gợi ý quản trị
                            </h2>
                            <p class="form-card-desc">
                                Một số lưu ý giúp dữ liệu danh mục đồng bộ và dễ quản lý hơn.
                            </p>
                        </div>
                    </div>

                    <div class="category-tip-box">
                        <ul class="category-tip-list">
                            <li>Không nên đổi slug nếu danh mục đã dùng ngoài website.</li>
                            <li>Tên danh mục nên ngắn gọn, dễ hiểu, tránh trùng lặp.</li>
                            <li>Chỉ tạm ẩn khi muốn ngưng hiển thị, không nên xóa vội.</li>
                        </ul>
                    </div>
                </section>

                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-solid fa-database"></i>
                                Ghi chú kỹ thuật
                            </h2>
                            <p class="form-card-desc">
                                Khu vực chừa sẵn để bạn nối backend hoặc hiển thị metadata sau này.
                            </p>
                        </div>
                    </div>

                    <div class="form-field">
                        <label>Mã danh mục</label>
                        <input type="text" class="form-input" value="#<?= (int)$category['id'] ?>" readonly>
                    </div>

                    <div class="form-field">
                        <label>Ngày tạo</label>
                        <input type="text" class="form-input" value="<?= htmlspecialchars($category['created_at']) ?>" readonly>
                    </div>

                    <div class="form-field form-field-no-margin">
                        <label>Hook backend</label>
                        <textarea class="form-textarea" rows="5" readonly>TODO:
                            - Lấy category theo ID
                            - Validate name / slug
                            - Update tbl_categories
                            - Toast + redirect manage-category
                        </textarea>
                    </div>
                </section>

            </aside>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('category_name_edit');
    const slugInput = document.getElementById('category_slug_edit');
    const statusInput = document.getElementById('category_status_edit');

    const previewName = document.getElementById('previewCategoryName');
    const previewSlug = document.getElementById('previewCategorySlug');
    const previewStatus = document.getElementById('previewCategoryStatus');
    const statusDot = document.querySelector('.category-status-dot');

    if (nameInput && previewName) {
        nameInput.addEventListener('input', function () {
            previewName.textContent = this.value.trim() || 'Tên danh mục';
        });
    }

    if (slugInput && previewSlug) {
        slugInput.addEventListener('input', function () {
            previewSlug.textContent = this.value.trim() || 'duong-dan-danh-muc';
        });
    }

    if (statusInput && previewStatus && statusDot) {
        statusInput.addEventListener('change', function () {
            if (this.value === '1') {
                previewStatus.textContent = 'Đang hoạt động';
                statusDot.classList.remove('is-hidden');
                statusDot.classList.add('is-active');
            } else {
                previewStatus.textContent = 'Tạm ẩn';
                statusDot.classList.remove('is-active');
                statusDot.classList.add('is-hidden');
            }
        });
    }
});
</script>