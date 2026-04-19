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
?>
<div class="wrapper">
    <div class="page-header">
        <h2 class="page-title">QUẢN LÝ SẢN PHẨM</h2>
    </div>

    <div class="toolbar">
        <a href="index.php?page=add-products" class="btn-add">
            <i class="fa-solid fa-plus"></i> Thêm sản phẩm mới
        </a>

        <div class="toolbar-right">
            <div class="filter-group">
                <label>Lọc danh mục</label>
                <select class="form-control">
                    <option>tất cả</option>
                    <option>Tấm pin</option>
                    <option>Biến tần</option>
                    <option>Pin lưu trữ</option>
                    <option>Phụ kiện & Khung giá đỡ</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Lọc thương hiệu</label>
                <select class="form-control">
                    <option>Tất cả</option>
                    <option>Canadian Solar</option>
                    <option>Jinko Solar</option>
                    <option>AE Solar</option>
                    <option>Longi Solar</option>
                    <option>Trina Solar</option>
                    <option>Growatt</option>
                    <option>Huawei</option>
                    <option>Deye</option>
                    <option>SMA</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Trạng thái</label>
                <select class="form-control">
                    <option>Đang bán</option>
                    <option>Ẩn</option>
                </select>
            </div>

            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" class="form-control" placeholder="Nhập tên sản phẩm hoặc mã SP...">
            </div>
        </div>
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
                            $status_html = ($sp['status'] == 1) 
                                ? '<span class="status-badge status-active" style = "padding: 10px">Đang bán</span>' 
                                : '<span class="status-badge status-hidden" style = "padding: 10px">Tạm ẩn</span>';
                            
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
                                            <a href="javascript:void(0)" class="btn-icon btn-delete-icon" title="Xóa" onclick="openModal(<?= $sp['id'] ?>, 'product')">
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
        <div class="pagination-container">
            <span class="page-info">(1/1 trang)</span>
            <div class="pagination">
                <a href="#" class="page-link disabled"><i class="fa-solid fa-angles-left"></i></a>
                <a href="#" class="page-link active">1</a>
                <a href="#" class="page-link disabled"><i class="fa-solid fa-angles-right"></i></a>
            </div>
        </div><br>
    </div>
</div>