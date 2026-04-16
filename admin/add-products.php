<?php
    include('../classes/products.php');
    include('../classes/categories.php');
    include('../classes/brands.php');

    $productManager = new Product($conn);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_save_product'])){
        $name = $_POST['products-name-add'];
        $slug = $_POST['slug-add'];
        $category_id = ($_POST['category_id'] != '') ? $_POST['category_id'] : "NULL";
        $brand_id = ($_POST['brand_id'] != '') ? $_POST['brand_id'] : "NULL";
        $price = $_POST['price'];
        $old_price = $_POST['old_price'];
        $stock = $_POST['stock'];
        $power_capacity = $_POST['power_capacity'];
        $warranty = $_POST['warranty'];
        $short_description = $_POST['short_description'];
        $status = $_POST['status'];

        if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != ""){
            $image_name = $_FILES['image']['name'];
            $ext = pathinfo($image_name, PATHINFO_EXTENSION);
            $image_name = "image_". time() . "_" . bin2hex(random_bytes(4)) .".".$ext;
            $source_path = $_FILES['image']['tmp_name'];
            $destination_path = "../uploads/products/images/".$image_name;
            $upload = move_uploaded_file($source_path,$destination_path);
        }
        else{
            $image_name = "default.jpg";
        }

        if(isset($_FILES['video']['name']) && $_FILES['video']['name'] != ""){
            $video_name = $_FILES['video']['name'];
            $ext = pathinfo($video_name, PATHINFO_EXTENSION);
            $video_name = "video_". time() . "_" . bin2hex(random_bytes(4)) .".".$ext;
            $source_path = $_FILES['video']['tmp_name'];
            $destination_path = "../uploads/products/videos/".$video_name;
            $video = move_uploaded_file($source_path,$destination_path);
        }
        else{
            $video_name = "";
        }

        $new_product_id = $productManager->addProduct($category_id, $brand_id, $name, $slug, $image_name, $video_name, $power_capacity, $price, $old_price, $warranty, $stock, $short_description, $status);
        // GỌI DATABASE LƯU SẢN PHẨM TRƯỚC
        if($new_product_id){
            if(isset($_FILES['gallery']['name']) && $_FILES['gallery']['name'][0] != ""){
                $count = count($_FILES['gallery']['name']);
                for($i=0;$i<$count;$i++){
                    $gal_name = $_FILES['gallery']['name'][$i];
                    $ext = pathinfo($gal_name, PATHINFO_EXTENSION);
                    $gal_name = "gallery_". time() . "_" . bin2hex(random_bytes(4)) .".".$ext;
                    $source_path = $_FILES['gallery']['tmp_name'][$i];
                    $destination_path = "../uploads/products/image_gallery/".$gal_name;
                    if(move_uploaded_file($source_path, $destination_path)){
                        $productManager->addGalleryImage($new_product_id, $gal_name);
                    }
                }
            }
        }

        if(!$new_product_id){
            $_SESSION['toast_message'] = "Lỗi tải file lên, vui lòng thử lại";
            $_SESSION['toast_type'] = 'error';
            header("Location: index.php?page=add-products"); 
            exit();
        }
        else{
            $_SESSION['toast_message'] = "Thêm sản phẩm thành công";
            $_SESSION['toast_type'] = 'success';   
            header("Location: index.php?page=add-products"); 
            exit(); 
        }
        
    }
    $categoryManager = new Category($conn);
    $brandManager = new Brand($conn);
    $productManager = new Product($conn);

    $listCategories = $categoryManager->getAllCategories();
    $listBrands = $brandManager->getAllBrands();
?>
<div class="wrapper">
    <div class="page-header-add">
        <h2 class="page-title-add">THÊM SẢN PHẨM MỚI</h2>
        <a href="index.php?page=manage-products" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div><br>

    <form action="" id="addProductForm" method="POST" enctype="multipart/form-data">
        <div class="form-layout-add">
            <div class="form-col-left-add">
                <div class="form-panel-add">
                    <h3 class="panel-title-add">1. THÔNG TIN CƠ BẢN</h3><br>

                    <div class="form-group-add">
                        <label class="form-label-add">Tên sản phẩm *</label>
                        <input type="text" id="products_name" name="products-name-add" class="form-control-add" required placeholder="VD: Tấm pin Canadian Solar 550W Mono">
                        <span class="error-msg" id="error_product_name"></span>
                    </div>

                    <div class="form-group-add">
                        <label class="form-label-add">Đường dẫn (Slug) *</label>
                        <input id="products_slug" type="text" name="slug-add" class="form-control-add" placeholder="VD: tam-pin-canadian-550w-mono">
                        <span class="error-msg" id="slug_error"></span>
                    </div>

                    <div class="form-group-add">
                        <label class="form-label-add">Mô tả chi tiết</label>
                        <textarea name="short_description" rows="5" class="form-control-add" placeholder="Nhập các đặc điểm nổi bật của sản phẩm..."></textarea>
                    </div>
                </div>

                <div class="form-panel-add">
                    <h3 class="panel-title-add">2. THÔNG SỐ & KHO HÀNG</h3><br>
                    <div class="form-row-add">
                        <div class="form-col-add">
                            <label class="form-label-add">Công suất (VD: 550W, 5kW)</label>
                            <input type="text" name="power_capacity" class="form-control-add" placeholder="Nhập công suất">
                        </div>

                        <div class="form-col-add">
                            <label class="form-label-add">Thời gian bảo hành</label>
                            <input type="text" name="warranty" class="form-control-add" placeholder="VD: Bảo hành 12 năm">
                        </div>
                    </div>

                    <div class="form-row-add">
                        <div class="form-col-add">
                            <label class="form-label-add">Giá bán (VNĐ) *</label>
                            <input type="number" id="products_price" name="price" value="0" class="form-control-add" required>
                            <span class="error-msg" id="price_error"></span>
                        </div>
                        <div class="form-col-add">
                            <label class="form-label-add">Giá gốc (VNĐ)</label>
                            <input type="number" name="old_price" class="form-control-add" placeholder="Để trống nếu không giảm">
                        </div>
                        <div class="form-col-add">
                            <label class="form-label-add">Số lượng tồn kho</label>
                            <input type="number" id="products_stock" name="stock" value="0" class="form-control-add">
                             <span class="error-msg" id="stock_error"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-col-right-add">
                <div class="form-panel-add">
                    <h3 class="panel-title-add">3. HÌNH ẢNH & VIDEO</h3><br>
                    <div class="form-group-add">
                        <label class="form-label-add">Ảnh đại diện chính *</label>
                        <div class="upload-box-add">
                            <i class="fa-regular fa-image upload-icon-add"></i><br>
                            <input type="file" id="main-image-upload" name="image" accept="image/*" class="file-input-add">
                            <div id="main-image-preview-container" style="display: flex; justify-content: center; margin-top: 15px;"></div>
                        </div>
                    </div>

                    <div class="form-group-add">
                        <label class="form-label-add">Thư viện ảnh (Chọn nhiều)</label>
                        <div class="upload-box-add">
                            <i class="fa-solid fa-images upload-icon-add"></i>
                            <input type="file" id="gallery-upload" name="gallery[]" multiple accept="image/*" class="file-input-add">
                            <div id="gallery-preview-container" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;"></div>
                        </div>
                    </div>

                    <div class="form-group-add">
                        <label class="form-label-add">Video giới thiệu</label>
                        <div class="upload-box-add">
                            <i class="fa-solid fa-video upload-icon-add"></i>
                            <input type="file" id="video-upload" name="video" accept="video/mp4,video/webm" class="file-input-add">
                            <div id="video-preview-container" style="display: flex; justify-content: center; margin-top: 15px;"></div>
                        </div>
                    </div>
                </div>

                <div class="form-panel-add">
                    <h3 class="panel-title-add">4. PHÂN LOẠI</h3><br>
                    <div class="form-group-add">
                        <label class="form-label-add">Danh mục</label>
                        <select name="category_id" class="form-control-add" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php
                                if (!empty($listCategories)) {
                                    foreach ($listCategories as $cat) {
                                        // Chỉ hiển thị danh mục đang hoạt động (status = 1)
                                        if ($cat['status'] == 1) {
                                            echo '<option value="' . $cat['id'] . '">' . $cat['name'] . '</option>';
                                        }
                                    }
                                }
                            ?>
                        </select>
                    </div>

                    <div class="form-group-add">
                        <label class="form-label-add">Thương hiệu</label>
                        <select name="brand_id" class="form-control-add">
                            <option value="">-- Chọn hãng --</option>
                            <?php
                                if (!empty($listBrands)){
                                    foreach ($listBrands as $brand) {
                                        // Chỉ hiển thị thương hiệu đang hoạt động (status = 1)
                                        if ($brand['status'] == 1) {
                                            echo '<option value="' . $brand['id'] . '">' . $brand['name'] . '</option>';
                                        }
                                    }
                                }
                            ?>
                        </select>
                    </div>

                    <div class="form-group-add">
                        <label class="form-label-add">Trạng thái</label>
                        <select name="status" class="form-control-add">
                            <option value="1">Đang bán (Công khai)</option>
                            <option value="0">Tạm ẩn (Nháp)</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="btn_save_product" id="btn-submit" class="btn-submit">
                    <i class="fa-solid fa-save"></i> Lưu Sản Phẩm Mới
                </button>
            </div>
        </div>
    </form>
</div>

<div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div class="spinner"></div><br>
    <h3 style="color: #10b981;">Đang tải dữ liệu...</h3>
    <p style="color: #64748b; font-size: 14px;">Vui lòng không đóng trình duyệt!</p>
</div>

