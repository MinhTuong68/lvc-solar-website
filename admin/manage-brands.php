<?php
    include('../classes/brands.php');
    $brandManager = new Brand($conn);
    // XỬ LÝ KHI BẤM NÚT LƯU THƯƠNG HIỆU
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_add_brand'])){
        $name = trim($_POST['brand_name']);
        $slug = trim($_POST['brand_slug']);
        $status = $_POST['status'];

        // --- XỬ LÝ UPLOAD LOGO ---
        $logo_name = "default_brand.png"; // Mặc định nếu không up ảnh
        
        if(isset($_FILES['logo']['name']) && $_FILES['logo']['name'] != ""){
            // Lấy đuôi file (jpg, png, webp...)
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            // Đổi tên file để không bị trùng (vd: brand_12345.png)
            $logo_name = "brand_".rand(0000, 9999).'.'.$ext;
            
            $source_path = $_FILES['logo']['tmp_name'];
            $destination_path = "../uploads/brands/images/".$logo_name;
            
            // Upload file vào thư mục
            move_uploaded_file($source_path, $destination_path);
        }

        // Gọi hàm lưu vào DB
        $result = $brandManager->addBrand($name, $slug, $logo_name, $status);
        
        if ($result == "success") {
            $_SESSION['toast_message'] = "Thêm thương hiệu thành công!";
            $_SESSION['toast_type'] = 'success';
        } else if ($result == "exists") {
            $_SESSION['toast_message'] = "Lỗi: Đường dẫn (Slug) này đã tồn tại!";
            $_SESSION['toast_type'] = 'error';
        } else {
            $_SESSION['toast_message'] = "Lỗi hệ thống, vui lòng thử lại!";
            $_SESSION['toast_type'] = 'error';
        }
        header("Location: index.php?page=manage-brands"); 
        exit();
    }

    // --- XỬ LÝ LẤY DỮ LIỆU & PHÂN TRANG ---
    $limit = 5; 
    $current_p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    if ($current_p < 1) $current_p = 1;
    $offset = ($current_p - 1) * $limit;

    $total_records = $brandManager->getTotalBrands();
    $total_pages = ceil($total_records / $limit);

    $listBrands = $brandManager->getBrandsPaginated($limit, $offset);

    // BẮT SỰ KIỆN ĐỔI TRẠNG THÁI THƯƠNG HIỆU
    if (isset($_GET['action']) && $_GET['action'] == 'toggle_status' && isset($_GET['id'])) {
        $cat_id = (int)$_GET['id'];
        $current_status = (int)$_GET['current'];
        
        // Dùng if - else rành mạch, dễ hiểu
        $new_status = 0;
        if ($current_status == 1) {
            $new_status = 0; // Nếu đang là 1 (Hiện) thì đổi thành 0 (Ẩn)
        } else {
            $new_status = 1; // Nếu đang là 0 (Ẩn) thì đổi thành 1 (Hiện)
        }

        // Cập nhật vào CSDL
        $stmt = $conn->prepare("UPDATE tbl_brands SET status = ? WHERE id = ?");
        
        if ($stmt) {
            $stmt->bind_param("ii", $new_status, $cat_id);
            $stmt->execute();
        }
        header("Location: index.php?page=manage-brands");
        exit();
    }
?>
<div class="wrapper">
    <div class="page-header-add">
        <h2 class="page-title-add">QUẢN LÝ THƯƠNG HIỆU</h2>
    </div><br>

    <form action="" id="addBrandForm" method="POST" enctype="multipart/form-data">
        <div class="form-layout-add2">
            
            <div class="form-col-right-add">
                <div class="form-panel-add">
                    <h3 class="panel-title-add"><i class="fa-solid fa-plus"></i> THÊM THƯƠNG HIỆU</h3><br>

                    <div class="form-group-add">
                        <label class="form-label-add">Tên thương hiệu *</label>
                        <input type="text" id="brand_name" name="brand_name" class="form-control-add" required placeholder="VD: Canadian Solar">
                        <span class="error-msg" id="error_brand_name"></span>
                    </div>

                    <div class="form-group-add">
                        <label class="form-label-add">Đường dẫn (Slug) *</label>
                        <input id="brand_slug" type="text" name="brand_slug" class="form-control-add" required placeholder="VD: canadian-solar">
                        <span class="error-msg" id="slug_brand_error"></span>
                    </div>

                    <div class="form-group-add">
                        <label class="form-label-add">Logo thương hiệu *</label>
                        <div class="upload-box-add">
                            <i class="fa-regular fa-image upload-icon-add"></i><br>
                            <input type="file" id="brand-logo-upload" name="logo" accept="image/*" class="file-input-add">
                            <div id="brand-logo-preview" style="display: flex; justify-content: center; margin-top: 15px;"></div>
                        </div>
                    </div>

                    <div class="form-group-add">
                        <label class="form-label-add">Trạng thái</label>
                        <select name="status" class="form-control-add">
                            <option value="1">Đang hoạt động</option>
                            <option value="0">Tạm ẩn</option>
                        </select>
                    </div>

                    <button type="submit" name="btn_add_brand" class="btn-submit" style="margin-top: 10px;">
                        <i class="fa-solid fa-save"></i> Lưu thương hiệu
                    </button>
                </div>
            </div>

            <div class="form-col-left-add">
                <div class="form-panel-add">
                     <h3 class="panel-title-add"><i class="fa-solid fa-list"></i> DANH SÁCH THƯƠNG HIỆU</h3><br>
                     
                     <table class="data-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th class="text-center">LOGO</th>
                                <th>TÊN THƯƠNG HIỆU</th>
                                <th>SLUG</th>
                                <th class="text-center">TRẠNG THÁI</th>
                                <th class="text-center">HÀNH ĐỘNG</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if (!empty($listBrands)){
                                    $stt = $offset + 1;
                                    foreach ($listBrands as $brand){
                                        $check_prod = $conn->prepare("SELECT COUNT(id) FROM tbl_products WHERE brand_id = ?");
                                        $check_prod->bind_param("i", $brand['id']);
                                        $check_prod->execute();
                                        $product_count = $check_prod->get_result()->fetch_row()[0];

                                         $eye_icon = ($brand['status'] == 1) ? 'fa-eye' : 'fa-eye-slash';
                                            $badge_class = ($brand['status'] == 1) ? 'status-active' : 'status-hidden';
                                            $badge_text = ($brand['status'] == 1) ? 'Hoạt động' : 'Tạm ẩn';
                                            $status_html = '
                                            <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                                <a href="index.php?page=manage-brands&action=toggle_status&id='.$brand['id'].'&current='.$brand['status'].'" style="color: #64748b; font-size: 1.1rem;" title="Đổi trạng thái">
                                                    <i class="fa-solid '.$eye_icon.'"></i>
                                                </a>
                                                <span class="status-badge '.$badge_class.'">'.$badge_text.'</span>
                                            </div>';
                                        ?>
                                            <tr>
                                                <td><?= $stt++; ?></td>
                                                <td class="text-center">
                                                    <div style="width: 80px; height: 50px; background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; padding: 2px;">
                                                        <img src="../uploads/brands/images/<?= $brand['logo'] ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;" alt="Logo">
                                                    </div>
                                                </td>
                                                <td class="fw-bold"><?= $brand['name'] ?></td>
                                                <td><?= $brand['slug'] ?></td>
                                                <td class="text-center"><?= $status_html ?></td>
                                                <td class="text-center">
                                                    <div class="action-btns">
                                                        <a href="#" class="btn-icon btn-edit-icon" title="Sửa"><i class="fa-solid fa-pen-to-square"></i></a>
                                                        <a href="javascript:void(0)" class="btn-icon btn-delete-icon" title="Xóa" onclick="openModal(<?= $brand['id'] ?>, 'brand')">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                    </div>        
                                                </td>
                                            </tr>
                                        <?php
                                    }
                                }
                                else{
                                    ?>
                                        <tr>
                                            <td colspan="6" class="empty-msg">Chưa có thương hiệu nào!</td>
                                        </tr>
                                    <?php
                                }
                            ?>
                        </tbody>
                    </table>
                     <?php
                        if ($total_pages > 1){
                            ?>
                                <div class="pagination-container">
                                    <span class="page-info">(<?= $current_p ?>/<?= $total_pages ?> trang)</span>
                                    <div class="pagination">
                                        <a href="?page=manage-brands&p=<?= ($current_p > 1) ? ($current_p - 1) : 1 ?>" class="page-link <?= ($current_p <= 1) ? 'disabled' : '' ?>">
                                            <i class="fa-solid fa-angles-left"></i>
                                        </a>

                                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                            <a href="?page=manage-brands&p=<?= $i ?>" class="page-link <?= ($current_p == $i) ? 'active' : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        <?php endfor; ?>

                                        <a href="?page=manage-brands&p=<?= ($current_p < $total_pages) ? ($current_p + 1) : $total_pages ?>" class="page-link <?= ($current_p >= $total_pages) ? 'disabled' : '' ?>">
                                            <i class="fa-solid fa-angles-right"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php
                        }
                     ?>
                </div>
            </div>
        </div>
    </form><br><br><br>
</div>

<script>
    autoSlug('brand_name', 'brand_slug');
</script>