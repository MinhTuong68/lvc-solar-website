<?php
    include('../classes/brands.php');
    $brandManager = new Brand($conn);

    // 1. Kiểm tra ID trên URL
    if (!isset($_GET['id'])) {
        header("Location: index.php?page=manage-brands");
        exit();
    }

    $id = (int)$_GET['id'];

    // 2. Lấy dữ liệu Thương hiệu hiện tại từ DB
    // Chỗ này em viết SQL trực tiếp cho nhanh, phòng khi class Brand của anh chưa có hàm getBrandByID
    $sql_get = "SELECT * FROM tbl_brands WHERE id = $id";
    $result = mysqli_query($conn, $sql_get);
    $brand = mysqli_fetch_assoc($result);

    if (!$brand) {
        $_SESSION['toast_message'] = "Thương hiệu không tồn tại!";
        $_SESSION['toast_type'] = 'error';
        header("Location: index.php?page=manage-brands");
        exit();
    }

    // 3. XỬ LÝ CẬP NHẬT KHI BẤM NÚT LƯU
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_edit_brand'])) {
        // Kiểm tra Token bảo mật
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['toast_message'] = "Lỗi bảo mật!";
            $_SESSION['toast_type'] = 'error';
            header("Location: index.php?page=manage-brands");
            exit();
        }

        $name = trim($_POST['brand_name']);
        $slug = trim($_POST['brand_slug']);
        $status = (int)$_POST['status'];
        $old_logo = $_POST['old_logo'];
        
        $logo_name = $old_logo; // Mặc định giữ logo cũ
        
        // NẾU CÓ CHỌN LOGO MỚI THÌ UPLOAD VÀ XÓA LOGO CŨ
        if (isset($_FILES['logo']['name']) && $_FILES['logo']['name'] != "") {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logo_name = "brand_".rand(0000, 9999).'.'.$ext;
            
            $source_path = $_FILES['logo']['tmp_name'];
            $destination_path = "../uploads/brands/images/".$logo_name;
            
            if (move_uploaded_file($source_path, $destination_path)) {
                // Xóa logo cũ khỏi ổ cứng (Không xóa nếu nó là ảnh mặc định)
                if ($old_logo != "" && $old_logo != "default_brand.png") {
                    $old_path = "../uploads/brands/images/".$old_logo;
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
            } else {
                $logo_name = $old_logo; // Nếu lỗi upload thì quay về logo cũ
            }
        }

        // Cập nhật CSDL
        $sql_update = "UPDATE tbl_brands SET name='$name', slug='$slug', logo='$logo_name', status=$status WHERE id=$id";
        if (mysqli_query($conn, $sql_update)) {
            $_SESSION['toast_message'] = "Cập nhật thương hiệu thành công!";
            $_SESSION['toast_type'] = 'success';
            header("Location: index.php?page=manage-brands");
            exit();
        } else {
            $_SESSION['toast_message'] = "Lỗi cập nhật, vui lòng thử lại!";
            $_SESSION['toast_type'] = 'error';
        }
    }
?>

<link rel="stylesheet" href="assets/css/edit.css">

<div class="form-page">
    <div class="form-container">

        <!-- THANH TOPBAR -->
        <div class="form-topbar">
            <div class="form-topbar-left">
                <div class="form-breadcrumb">
                    <a href="index.php?page=manage-brands">Thương hiệu</a>
                    <span>/</span>
                    <span>Chỉnh sửa thương hiệu</span>
                </div>

                <h1 class="form-page-title">Cập nhật thương hiệu</h1>

                <div class="form-page-meta">
                    <span class="form-chip form-chip-neutral">ID: #<?= $brand['id'] ?></span>

                    <?php if ($brand['status'] == 1): ?>
                        <span class="form-chip form-chip-success">Đang hoạt động</span>
                    <?php else: ?>
                        <span class="form-chip form-chip-muted">Tạm ẩn</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-topbar-right">
                <a href="index.php?page=manage-brands" class="form-btn form-btn-light">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Quay lại</span>
                </a>

                <button type="submit" form="editBrandForm" name="btn_edit_brand" class="form-btn form-btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Lưu thay đổi</span>
                </button>
            </div>
        </div>

        <!-- FORM CHỈNH SỬA -->
        <form id="editBrandForm" action="" method="POST" enctype="multipart/form-data" class="form-layout">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-main">
                <section class="form-card">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-solid fa-tag"></i>
                                Thông tin thương hiệu
                            </h2>
                            <p class="form-card-desc">
                                Cập nhật tên và đường dẫn SEO cho thương hiệu.
                            </p>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="brand_name_edit">Tên thương hiệu <span class="form-required">*</span></label>
                        <input id="brand_name_edit" type="text" name="brand_name" class="form-input" value="<?= htmlspecialchars($brand['name']) ?>" required>
                    </div>

                    <div class="form-field">
                        <label for="brand_slug_edit">Đường dẫn (slug) <span class="form-required">*</span></label>
                        <input id="brand_slug_edit" type="text" name="brand_slug" class="form-input" value="<?= htmlspecialchars($brand['slug']) ?>" required>
                    </div>

                    <div class="form-field form-field-no-margin">
                        <label for="brand_status_edit">Trạng thái</label>
                        <select id="brand_status_edit" name="status" class="form-select">
                            <option value="1" <?= ($brand['status'] == 1) ? 'selected' : '' ?>>Đang hoạt động</option>
                            <option value="0" <?= ($brand['status'] == 0) ? 'selected' : '' ?>>Tạm ẩn</option>
                        </select>
                    </div>
                </section>
            </div>

            <!-- CỘT BÊN PHẢI (LOGO) -->
            <aside class="form-sidebar">
                <section class="form-card form-card-sticky">
                    <div class="form-card-head">
                        <div>
                            <h2 class="form-card-title">
                                <i class="fa-regular fa-image"></i>
                                Logo Thương hiệu
                            </h2>
                            <p class="form-card-desc">Cập nhật logo nhận diện.</p>
                        </div>
                    </div>

                    <div class="form-media-box">
                        <div class="form-media-preview" style="background: #fff; padding: 20px;">
                            <img src="../uploads/brands/images/<?= $brand['logo'] ?>" alt="Chưa có logo" style="object-fit: contain;">
                        </div><br>

                        <!-- Lưu lại tên logo cũ để PHP biết -->
                        <input type="hidden" name="old_logo" value="<?= $brand['logo'] ?>">

                        <div class="form-field form-field-no-margin">
                            <input type="file" name="logo" class="form-file" accept="image/*">
                            <small class="form-note">Để trống nếu muốn giữ nguyên logo cũ.</small>
                        </div>
                    </div>
                </section>
            </aside>
        </form>
    </div>
</div>

<script>
    // Gọi hàm tự động tạo slug khi gõ tên
    autoSlug('brand_name_edit', 'brand_slug_edit');
</script>