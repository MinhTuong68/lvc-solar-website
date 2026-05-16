<?php
    include('../classes/categories.php');
    include('../classes/brands.php');
    include('../classes/products.php');

    $categoryManager = new Category($conn);
    $brandManager = new Brand($conn);
    $productManager = new Product($conn);

    $listCategories = $categoryManager->getAllCategories();
    $listBrands = $brandManager->getAllBrands();
    $listProducts = $productManager->getAllProducts();

    if (isset($_GET['action']) && $_GET['action'] == 'toggle_status' && isset($_GET['id'])) {
        
        $product_id = (int)$_GET['id'];
        $current_status = (int)$_GET['current'];
    
        $new_status = 0;
        if ($current_status == 1) {
            $new_status = 0;
        } else {
            $new_status = 1;
        }

        $stmt = $conn->prepare("UPDATE tbl_products SET status = ? WHERE id = ?");
        
        if ($stmt) {
            $stmt->bind_param("ii", $new_status, $product_id);
            $stmt->execute();
        }
        header("Location: index.php?page=manage-products");
        exit();
    }

    $search         = isset($_GET['q']) ? trim($_GET['q']) : '';
    $filter_cat     = isset($_GET['category_id']) ? $_GET['category_id'] : '';
    $filter_brand   = isset($_GET['brand_id']) ? $_GET['brand_id'] : '';
    $filter_status  = isset($_GET['status']) ? $_GET['status'] : '';

    if ($search !== '' || $filter_cat !== '' || $filter_brand !== '' || $filter_status !== '') {
        
        $filteredProducts = [];
        
        foreach ($listProducts as $p) {
            $is_match = true;

            if ($search !== '') {
                if (stripos($p['name'], $search) === false && stripos($p['slug'], $search) === false) {
                    $is_match = false; 
                }
            }

            if ($filter_cat !== '' && $p['category_id'] != $filter_cat) {
                $is_match = false;
            }

            if ($filter_brand !== '' && $p['brand_id'] != $filter_brand) {
                $is_match = false;
            }

            if ($filter_status !== '' && $p['status'] != $filter_status) {
                $is_match = false;
            }

            if ($is_match == true) {
                $filteredProducts[] = $p;
            }
        }
        
        $listProducts = $filteredProducts; 
    }
    $limit = 20;
    $current_p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    if ($current_p < 1) $current_p = 1;
    $total = count($listProducts);
    $total_pages = ceil($total / $limit);
    $offset = ($current_p - 1) * $limit;
    $listProducts = array_slice($listProducts, $offset, $limit);
?>
<div class="wrapper">
    <div class="page-header">
        <h2 class="page-title">QUẢN LÝ SẢN PHẨM</h2>
    </div>

    <div class="toolbar">
        <a href="index.php?page=add-products" class="btn-add">
            <i class="fa-solid fa-plus"></i> Thêm sản phẩm mới
        </a>

        <form method="GET" action="" class="toolbar-right">
            <input type="hidden" name="page" value="manage-products">
            <div class="filter-group">
                <label>Lọc danh mục</label>
                <select name="category_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Tất cả danh mục</option>
                    <?php 
                    // Kiểm tra xem trên URL người dùng có đang chọn danh mục nào không
                    $selected_cat = isset($_GET['category_id']) ? $_GET['category_id'] : '';
                    
                    if (!empty($listCategories)) {
                        foreach ($listCategories as $cat) {
                            // Nếu đang chọn đúng danh mục này thì in thêm thuộc tính 'selected' để giữ nguyên
                            $selected = ($cat['id'] == $selected_cat) ? 'selected' : '';
                            echo "<option value='{$cat['id']}' {$selected}>{$cat['name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Lọc thương hiệu</label>
                <select name="brand_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Tất cả thương hiệu</option>
                    <?php 
                    // Kiểm tra xem trên URL người dùng có đang chọn thương hiệu nào không
                    $selected_brand = isset($_GET['brand_id']) ? $_GET['brand_id'] : '';
                    
                    if (!empty($listBrands)) {
                        foreach ($listBrands as $brand) {
                            $selected = ($brand['id'] == $selected_brand) ? 'selected' : '';
                            echo "<option value='{$brand['id']}' {$selected}>{$brand['name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Trạng thái</label>
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <?php $selected_status = isset($_GET['status']) ? $_GET['status'] : ''; ?>
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" <?= ($selected_status === '1') ? 'selected' : '' ?>>Đang bán (Hoạt động)</option>
                    <option value="0" <?= ($selected_status === '0') ? 'selected' : '' ?>>Đã ẩn</option>
                </select>
            </div>

            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Nhập tên sản phẩm hoặc mã SP...">
            </div>
        </form>
    </div><br><br>
    <div class="form-panel-add" style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; padding: 0;">
        <table class="data-table-products">
            <thead>
                <tr>
                    <th class="text-center">STT</th>
                    <th class="text-center">Ảnh sản phẩm</th>
                    <th>Tên sản phẩm & Mã (Slug)</th>
                    <th>Thông số</th>
                    <th>Giá (VNĐ)</th>
                    <th>Đã bán</th>
                    <th>Kho</th>
                    <th>Phân loại</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if (!empty($listProducts)){
                        $stt = 1;
                        foreach ($listProducts as $sp){

                            $eye_icon = ($sp['status'] == 1) ? 'fa-eye' : 'fa-eye-slash';
                            $badge_class = ($sp['status'] == 1) ? 'status-active' : 'status-hidden';
                            $badge_text = ($sp['status'] == 1) ? 'Hoạt động' : 'Tạm ẩn';
                            $status_html = '
                            <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                <a href="index.php?page=manage-products&action=toggle_status&id='.$sp['id'].'&current='.$sp['status'].'" style="color: #64748b; font-size: 1.1rem;" title="Đổi trạng thái">
                                    <i class="fa-solid '.$eye_icon.'"></i>
                                </a>
                                <span class="status-badge '.$badge_class.'">'.$badge_text.'</span>
                            </div>';
                            
                            // Xử lý Giá Bán: Thêm ký hiệu '₫' và xử lý nếu giá = 0
                            if ($sp['price'] > 0) {
                                $price_format = number_format($sp['price'], 0, ',', '.') . '<span class = "price_main">₫</span>';
                            } else {
                                $price_format = '<span class = "price_contact">Liên hệ</span>';
                            }

                            // Xử lý Giá Cũ: Thêm ký hiệu '₫'
                            $old_price_html = '';
                            if ($sp['old_price'] > 0) {
                                $old_price_html = '<div class = "price_old">' . number_format($sp['old_price'], 0, ',', '.') . ' ₫</div>';
                            }
                            $cat_name = $sp['category_name'] ? $sp['category_name'] : '<span class = "text-danger">Chưa chọn</span>';
                            $brand_name = $sp['brand_name'] ? $sp['brand_name'] : 'Không có';
                            ?>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td class="text-center align-middle"><?= $stt++ ?></td>
                                    
                                    <td class="text-center align-middle">
                                        <div class="product-img-wrap">
                                            <img src="../uploads/products/images/<?= $sp['image'] ?>" class="img_product_logo" alt="Ảnh">
                                            <?php if($sp['video'] != ''): ?>
                                                <div class="video-badge" title="Có video">
                                                    <i class="fa-solid fa-play"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="fw-bold text-spec text-spec-margin"><?= $sp['name'] ?></div>
                                        <div style="color: #64748b; font-size: 15px;">Mã: <?= $sp['slug'] ?></div>
                                    </td>

                                    <td class="align-middle">
                                        <div class="text-spec text-spec-margin">
                                            <i class="fa-solid fa-bolt text-yellow-500" style="color: #eab308; margin-right: 5px;"></i><?= $sp['power_capacity'] ?: 'N/A' ?>
                                        </div>
                                        <div class="text-spec text-spec-margin">
                                            <i class="fa-solid fa-shield-halved text-green-500" style="color: #10b981; margin-right: 5px;"></i>BH: <?= $sp['warranty'] ?: 'N/A' ?>
                                        </div>
                                    </td>

                                    <td class="text-right align-middle">
                                        <div style="color: #ef4444; font-weight: bold; font-size: 15px;"><?= $price_format ?></div>
                                        <?= $old_price_html ?>
                                    </td>

                                    <td class="">
                                        <?php echo $sp['sold'] ?>
                                    </td>

                                    <td class="fw-bold align-middle text-spec">
                                        <?= $sp['stock'] ?>
                                    </td>

                                    <td class="align-middle">
                                        <div class="text-spec-cat"><?= $cat_name ?></div>
                                        <?php
                                            $logo_path = '../uploads/brands/images/' . $sp['brand_logo'];
                                            if (!empty($sp['brand_logo'])  && file_exists($logo_path)){
                                                ?>
                                                    <div class="product-brand">
                                                        <img src="../uploads/brands/images/<?= $sp['brand_logo'] ?>" class="brand-logo-img" alt="Logo">
                                                    </div>
                                                <?php
                                            }
                                            else{
                                                ?>
                                                    <div class="brand-text-only">
                                                        <?= $brand_name ?>
                                                    </div>
                                                <?php
                                            }
                                        ?>            
                                    </td>

                                    <td class="align-middle">
                                        <?= $status_html ?>
                                    </td>

                                    <td class="align-middle">
                                        <div class="action-btns">
                                            <a href="index.php?page=edit-product&id=<?= $sp['id'] ?>" class="btn-icon btn-edit-icon" title="Sửa"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <a href="actions/delete.php?type=product&id=<?= $sp['id'] ?>" class="btn-icon btn-delete-icon" title="Xóa" onclick="event.preventDefault(); let urlXoa = this.href; openModal('Xác nhận xóa sản phẩm?', 'Bạn có chắc chắn muốn xóa sản phẩm này không? Toàn bộ dữ liệu, hình ảnh và thông số của sản phẩm sẽ bị xóa vĩnh viễn khỏi hệ thống!', 'fa-solid fa-trash', 'Xóa sản phẩm', function() { window.location.href = urlXoa; })">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                        }
                    } else{
                        ?>
                            <tr>
                                <td colspan="9" class="empty-msg" style="text-align: center; padding: 30px; color: #94a3b8; font-size: 15px;">
                                    <i class="fa-solid fa-box-open" style="font-size: 40px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                                    Chưa có sản phẩm nào trong kho!
                                </td>
                            </tr>
                        <?php
                    }
                ?>
            </tbody>
        </table>  
        <?php
            $query_string = $_GET;
            unset($query_string['p']);
            $base_url = '?' . http_build_query($query_string) . '&p=';
        ?>
        <div class="pagination-container">
            <span class="page-info">(<?= $current_p ?>/<?= $total_pages ?> trang)</span>
            <div class="pagination">
                <a href="<?= $base_url.(($current_p>1)?$current_p-1:1) ?>" class="page-link <?= $current_p<=1?'disabled':'' ?>">
                    <i class="fa-solid fa-angles-left"></i>
                </a>
                <?php for($i=1;$i<=$total_pages;$i++): ?>
                    <a href="<?= $base_url.$i ?>" class="page-link <?= $current_p==$i?'active':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a href="<?= $base_url.(($current_p<$total_pages)?$current_p+1:$total_pages) ?>" class="page-link <?= $current_p>=$total_pages?'disabled':'' ?>">
                    <i class="fa-solid fa-angles-right"></i>
                </a>
            </div>
        </div><br>
    </div>
</div>