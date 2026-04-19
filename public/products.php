<?php
    // 1. GỌI CÁC FILE CLASS OOP VÀO TRANG
    include ('../classes/categories.php');
    include ('../classes/brands.php');
    include ('../classes/products.php');

    $categoryObj = new Category($conn);
    $brandObj    = new Brand($conn);
    $productObj  = new Product($conn);

    $categories  = $categoryObj->getAllCategories();
    $brands      = $brandObj->getAllBrands();
    $allProducts = $productObj->getAllProducts();

    $catSlug = isset($_GET['cat']) ? trim($_GET['cat']) : '';
    $search  = isset($_GET['q']) ? trim($_GET['q']) : '';
    $sort    = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

    $selectedPowers = (isset($_GET['power']) && is_array($_GET['power'])) ? $_GET['power'] : [];
    $selectedPrices = (isset($_GET['price']) && is_array($_GET['price'])) ? $_GET['price'] : [];
    $selectedTypes  = (isset($_GET['type']) && is_array($_GET['type'])) ? $_GET['type'] : [];

    $selectedBrands = (isset($_GET['brand']) && is_array($_GET['brand'])) ? $_GET['brand'] : [];

    // -- FIX LỖI LỌC DANH MỤC: Tìm ID của danh mục từ Slug --
    $selectedCatId = null;
    if ($catSlug !== '') {
        foreach ($categories as $c) {
            if ($c['slug'] === $catSlug) {
                $selectedCatId = $c['id'];
                break;
            }
        }
    }

    $totalActiveProducts = 0;
    $filteredProducts = [];

    foreach ($allProducts as $p) {
        if ($p['status'] == 1 && ($p['category_status'] === null || $p['category_status'] == 1)) {
            $totalActiveProducts++; 
            
            if ($selectedCatId !== null && $p['category_id'] != $selectedCatId) continue;
            if ($search !== '' && stripos($p['name'], $search) === false) continue;
            if (!empty($selectedBrands) && !in_array($p['brand_id'], $selectedBrands)) continue;
            
            // ==========================================
            // LOGIC MỚI: KIỂM TRA LỌC CÔNG SUẤT, GIÁ, LOẠI
            // ==========================================
            
            // 1. Lọc Công Suất (BÓC TÁCH SỐ ĐỂ SO SÁNH)
            if (!empty($selectedPowers)) {
                $powerMatch = false;
                
                // Bóc tách SỐ từ dữ liệu trong Database (VD: "Đèn 25 W", "25", "25w" -> đều bóc ra số 25)
                preg_match('/\d+/', $p['power_capacity'], $dbMatches);
                $dbPower = isset($dbMatches[0]) ? (int)$dbMatches[0] : -1;

                foreach ($selectedPowers as $pow) {
                    // Bóc tách SỐ từ value của ô Checkbox (VD: Checkbox "25w" -> bóc ra số 25)
                    preg_match('/\d+/', $pow, $filterMatches);
                    $filterPower = isset($filterMatches[0]) ? (int)$filterMatches[0] : -2;

                    // So sánh 2 con số nguyên với nhau (Tránh lỗi 20w hiển thị nhầm cho 120w)
                    if ($dbPower === $filterPower) {
                        $powerMatch = true;
                        break;
                    }
                }
                if ($powerMatch == false) continue; // Nếu không khớp thì bỏ qua SP này
            }

            // 2. Lọc Mức Giá (Xử lý các khoảng giá)
            if (!empty($selectedPrices)) {
                $priceMatch = false;
                $p_price = (float)$p['price']; // Lấy cột giá trong CSDL
                
                foreach ($selectedPrices as $pr) {
                    if ($pr === '0-1m' && $p_price < 1000000) { $priceMatch = true; break; }
                    elseif ($pr === '1m-2m' && $p_price >= 1000000 && $p_price < 2000000) { $priceMatch = true; break; }
                    elseif ($pr === '2m-3m' && $p_price >= 2000000 && $p_price < 3000000) { $priceMatch = true; break; }
                    elseif ($pr === '3m-5m' && $p_price >= 3000000 && $p_price < 5000000) { $priceMatch = true; break; }
                    elseif ($pr === '5m-max' && $p_price >= 5000000) { $priceMatch = true; break; }
                }
                if ($priceMatch == false) continue;
            }

            // 3. Lọc Loại Sản Phẩm (Tìm từ khóa 'đường', 'pha', 'ốp trần' trong tên SP)
            if (!empty($selectedTypes)) {
                $typeMatch = false;
                $p_name_lower = mb_strtolower($p['name']); // Chuyển tên SP thành chữ thường
                
                foreach ($selectedTypes as $t) {
                    if ($t === 'den-op-tran' && mb_strpos($p_name_lower, 'ốp trần') !== false) { $typeMatch = true; break; }
                    elseif ($t === 'den-bulb' && mb_strpos($p_name_lower, 'bulb') !== false) { $typeMatch = true; break; }
                    elseif ($t === 'den-phi-thuyen' && mb_strpos($p_name_lower, 'phi thuyền') !== false) { $typeMatch = true; break; }
                    elseif ($t === 'den-duong' && mb_strpos($p_name_lower, 'đường') !== false) { $typeMatch = true; break; }
                    elseif ($t === 'den-pha-nlmt' && mb_strpos($p_name_lower, 'pha năng lượng mặt trời') !== false) { $typeMatch = true; break; }
                    elseif ($t === 'den-cong-trinh' && mb_strpos($p_name_lower, 'công trình') !== false) { $typeMatch = true; break; }
                    elseif ($t === 'den-dia-bay' && mb_strpos($p_name_lower, 'đĩa bay') !== false) { $typeMatch = true; break; }
                    elseif ($t === 'den-pha' && mb_strpos($p_name_lower, 'pha') !== false) { $typeMatch = true; break; }
                    elseif ($t === 'den-ban-chai' && mb_strpos($p_name_lower, 'bàn chải') !== false) { $typeMatch = true; break; }
                    elseif ($t === 'den-lien-the' && mb_strpos($p_name_lower, 'liền thể') !== false) { $typeMatch = true; break; }
                }
                if ($typeMatch == false) continue;
            }

            // Nếu vượt qua mọi bộ lọc, đưa vào mảng hiển thị
            $filteredProducts[] = $p;
        }
    }
    // 6. XỬ LÝ SẮP XẾP SẢN PHẨM (Dùng hàm usort của PHP để sắp xếp mảng)
    if ($sort === 'price_asc') {
        usort($filteredProducts, function($a, $b) { return $a['price'] <=> $b['price']; });
    } elseif ($sort === 'price_desc') {
        usort($filteredProducts, function($a, $b) { return $b['price'] <=> $a['price']; });
    } elseif ($sort === 'name') {
        usort($filteredProducts, function($a, $b) { return strcmp($a['name'], $b['name']); });
    }
    // (Mặc định 'newest' không cần sort vì getAllProducts đã lấy theo ID DESC từ DB)

    // 7. Tổng sản phẩm sau khi đã lọc xong
    $total = count($filteredProducts);
?>
<!-- Breadcrumb -->
<div class="breadcrumb-bar">
  <div class="container">
    <div class="breadcrumb">
      <a href="?page=home">Trang chủ</a><span class="sep">/</span>
      <span class="current">Sản phẩm</span>
    </div>
  </div>
</div>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h1>Vật Tư & Sản Phẩm Solar</h1>
    <p>Phân phối chính hãng tấm pin, biến tần, pin lưu trữ từ các thương hiệu hàng đầu thế giới</p>
  </div>
</div>

<section class="products-section">
    <div class="container">
        <div class="products-grid-layout" style="">
            <aside>
                 <!-- Search -->
                <div class="widget-panel widget-search">
                    <form method="GET" action="">
                        <input type="hidden" name="page" value="products">
                        <?php if ($catSlug): ?>
                            <input type="hidden" name="cat" value="<?= e($catSlug) ?>">
                        <?php endif; ?>
                        
                        <div class="search-input-group">
                            <input type="text" name="q" value="<?= e($search) ?>" class="search-control" placeholder="Tìm sản phẩm...">
                            <button type="submit" class="search-submit-btn" title="Tìm kiếm">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="widget-panel">
                    <h4 class="widget-title">Danh mục</h4>

                    <?php
                        $catDisplayCount = 1;
                        if (!empty($categories)) {
                            foreach ($categories as $c) {
                                if ($c['status'] == 1) {
                                    $catDisplayCount++;
                                }
                            }
                        }
                    ?>

                    <div class="cat-list-container">
                        <a href="?page=products" class="cat-list-item cat-list-item--first <?= empty($catSlug) ? 'active' : '' ?>">
                            Tất cả <span class="cat-count-badge"><?= $totalActiveProducts ?></span>
                        </a>

                        <?php
                            $sortedCategories = [];

                            if (!empty($categories)) {
                                foreach ($categories as $cat) {
                                    if ($cat['status'] == 1) {
                                        $count = $productObj->countActiveByCategory($allProducts, $cat['id']);
                                        if ($count <= 0) continue;
                                        $sortedCategories[] = [
                                            'id'    => $cat['id'],
                                            'name'  => $cat['name'],
                                            'slug'  => $cat['slug'],
                                            'count' => $count
                                        ];
                                    }
                                }
                            }

                            usort($sortedCategories, function($a, $b) {
                                return $b['count'] <=> $a['count'];
                            });

                            foreach ($sortedCategories as $cat) {
                                $catActive = ($catSlug === $cat['slug']) ? 'active' : '';
                        ?>
                            <a href="?page=products&cat=<?= e($cat['slug']) ?>" class="cat-list-item <?= $catActive ?>">
                                <?= e($cat['name']) ?> <span class="cat-count-badge"><?= $cat['count'] ?></span>
                            </a>
                        <?php
                            }
                        ?>
                    </div>

                    <?php 
                        if ($catDisplayCount >=10){
                        ?>
                            <div class="cat-toggle-btn">Xem thêm <i class="fa-solid fa-angle-down"></i></div>
                        <?php
                        }
                        else{

                        }
                    ?>
                </div>

                <?php if (!empty($brands)): ?>
                <div class="widget-panel widget-panel--brands">
                    <h4 class="widget-title">Thương hiệu</h4>
                    <?php
                        $brandDisplayCount = 0;
                        foreach ($brands as $b) {
                            if ($b['status'] == 1) {
                                $brandDisplayCount++;
                            }
                        }
                    ?>
                    <form method="GET" action="">
                        <input type="hidden" name="page" value="products">
                        <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= e($catSlug) ?>"><?php endif; ?>
                        <?php if ($search): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
                        <?php if ($sort !== 'newest'): ?><input type="hidden" name="sort" value="<?= e($sort) ?>"><?php endif; ?>
                        
                        <div class="brand-list-container">
                            <?php
                                $sortedBrands = [];

                                foreach ($brands as $b) {
                                    if ($b['status'] == 1) {
                                        $brandCount = 0;

                                        foreach ($allProducts as $p) {
                                            if ($p['status'] == 1 && ($p['category_status'] === null || $p['category_status'] == 1) && $p['brand_id'] == $b['id']) {
                                                $brandCount++;
                                            }
                                        }
                                        if ($brandCount <= 0) continue;
                                        $sortedBrands[] = [
                                            'id'    => $b['id'],
                                            'name'  => $b['name'],
                                            'count' => $brandCount
                                        ];
                                    }
                                }

                                usort($sortedBrands, function($a, $b) {
                                    return $b['count'] <=> $a['count'];
                                });

                                foreach ($sortedBrands as $b) {
                                    $isChecked = in_array($b['id'], $selectedBrands) ? 'checked' : '';
                            ?>
                                <label class="brand-filter-label">
                                    <input type="checkbox" class="brand-filter-check" name="brand[]" value="<?= $b['id'] ?>" <?= $isChecked ?>>
                                    <?= e($b['name']) ?>
                                </label>
                            <?php
                                }
                            ?>
                        </div>
                    </form>
                    <?php 
                        if ($brandDisplayCount >= 10){
                            ?>
                                <div class="brand-toggle-btn">Xem thêm <i class="fa-solid fa-angle-down"></i></div>
                            <?php 
                        }
                        else{

                        }
                    ?>
                </div>
                <?php endif; ?>

                <div class="widget-panel">
                    <h3 class="widget-title">CÔNG SUẤT</h3>
                    <div class="brand-list-container">
                        <label class="brand-filter-label">
                            <input type="checkbox" name="power[]" value="25w" class="brand-filter-check" <?= in_array('25w', $selectedPowers) ? 'checked' : '' ?>> 25W
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="power[]" value="60w" class="brand-filter-check" <?= in_array('60w', $selectedPowers) ? 'checked' : '' ?>> 60W
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="power[]" value="100w" class="brand-filter-check" <?= in_array('100w', $selectedPowers) ? 'checked' : '' ?>> 100W
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="power[]" value="200w" class="brand-filter-check" <?= in_array('200w', $selectedPowers) ? 'checked' : '' ?>> 200W
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="power[]" value="300w" class="brand-filter-check" <?= in_array('300w', $selectedPowers) ? 'checked' : '' ?>> 300W
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="power[]" value="400w" class="brand-filter-check" <?= in_array('400w', $selectedPowers) ? 'checked' : '' ?>> 400W
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="power[]" value="500w" class="brand-filter-check" <?= in_array('500w', $selectedPowers) ? 'checked' : '' ?>> 500W
                        </label>
                    </div>
                    <div class="brand-toggle-btn">Xem thêm <i class="fa-solid fa-angle-down"></i></div>
                </div>

                <div class="widget-panel">
                    <h3 class="widget-title">MỨC GIÁ</h3>
                    <div class="brand-list-container">
                        <label class="brand-filter-label">
                            <input type="checkbox" name="price[]" value="0-1m" class="brand-filter-check" <?= in_array('0-1m', $selectedPrices) ? 'checked' : '' ?>> Dưới 1.000.000đ
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="price[]" value="1m-2m" class="brand-filter-check" <?= in_array('1m-2m', $selectedPrices) ? 'checked' : '' ?>> 1.000.000đ - 2.000.000đ
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="price[]" value="2m-3m" class="brand-filter-check" <?= in_array('2m-3m', $selectedPrices) ? 'checked' : '' ?>> 2.000.000đ - 3.000.000đ
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="price[]" value="3m-5m" class="brand-filter-check" <?= in_array('3m-5m', $selectedPrices) ? 'checked' : '' ?>> 3.000.000đ - 5.000.000đ
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="price[]" value="5m-max" class="brand-filter-check" <?= in_array('5m-max', $selectedPrices) ? 'checked' : '' ?>> Trên 5.000.000đ
                        </label>
                    </div>
                </div>
                <div class="widget-panel">
                    <h3 class="widget-title">LOẠI SẢN PHẨM</h3>
                    <div class="brand-list-container">
                        <label class="brand-filter-label">
                            <input type="checkbox" name="type[]" value="den-op-tran" class="brand-filter-check" <?= in_array('den-op-tran', $selectedTypes) ? 'checked' : '' ?>> Đèn Ốp Trần
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="type[]" value="den-bulb" class="brand-filter-check" <?= in_array('den-bulb', $selectedTypes) ? 'checked' : '' ?>> Đèn Bulb
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="type[]" value="den-phi-thuyen" class="brand-filter-check" <?= in_array('den-phi-thuyen', $selectedTypes) ? 'checked' : '' ?>> Đèn Phi Thuyền
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="type[]" value="den-duong" class="brand-filter-check" <?= in_array('den-duong', $selectedTypes) ? 'checked' : '' ?>> Đèn Đường
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="type[]" value="den-pha-nlmt" class="brand-filter-check" <?= in_array('den-pha-nlmt', $selectedTypes) ? 'checked' : '' ?>> Đèn pha năng lượng mặt trời
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="type[]" value="den-cong-trinh" class="brand-filter-check" <?= in_array('den-cong-trinh', $selectedTypes) ? 'checked' : '' ?>> Đèn Công Trình
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="type[]" value="den-dia-bay" class="brand-filter-check" <?= in_array('den-dia-bay', $selectedTypes) ? 'checked' : '' ?>> Đèn Đĩa Bay
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="type[]" value="den-pha" class="brand-filter-check" <?= in_array('den-pha', $selectedTypes) ? 'checked' : '' ?>> Đèn Pha
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="type[]" value="den-ban-chai" class="brand-filter-check" <?= in_array('den-ban-chai', $selectedTypes) ? 'checked' : '' ?>> Đèn Bàn Chải
                        </label>
                        <label class="brand-filter-label">
                            <input type="checkbox" name="type[]" value="den-lien-the" class="brand-filter-check" <?= in_array('den-lien-the', $selectedTypes) ? 'checked' : '' ?>> Đèn Liền Thể
                        </label>
                    </div>
                    <div class="brand-toggle-btn">Xem thêm <i class="fa-solid fa-angle-down"></i></div>
                </div>
            </aside>
            <div>
                <div class="products-toolbar">
                    <span class="products-count-text">Tìm thấy <strong><?= $total ?></strong> sản phẩm</span>
                    
                    <div class="toolbar-sort-group">
                        <label class="toolbar-sort-label">Sắp xếp:</label>
                        
                        <form method="GET" action="" class="toolbar-sort-form">
                            <input type="hidden" name="page" value="products">
                            
                            <?php 
                                if ($catSlug != '') { 
                                    ?>
                                        <input type="hidden" name="cat" value="<?php echo e($catSlug); ?>">
                                    <?php 
                                } 
                            ?>
                            
                            <?php 
                                if ($search != '') { 
                                    ?>
                                        <input type="hidden" name="q" value="<?php echo e($search); ?>">
                                    <?php 
                                } 
                            ?>
                            
                            <select name="sort" class="form-control toolbar-sort-select">
                                
                                <option value="newest" 
                                    <?php 
                                        if($sort == 'newest') { 
                                            echo 'selected'; 
                                            } 
                                    ?>>Mới nhất
                                </option>
                                
                                <option value="price_asc" <?php if($sort == 'price_asc') { echo 'selected'; } ?>>
                                    Giá tăng dần
                                </option>
                                
                                <option value="price_desc" <?php if($sort == 'price_desc') { echo 'selected'; } ?>>
                                    Giá giảm dần
                                </option>
                                
                                <option value="name" <?php if($sort == 'name') { echo 'selected'; } ?>>
                                    Tên A-Z
                                </option>
                                
                            </select>
                        </form>
                    </div>
                </div>

                <div class="products-data-grid">
                    <?php 
                        // BƯỚC 1: Kiểm tra xem mảng sản phẩm có rỗng không
                        if (empty($filteredProducts)) { 
                        ?>
                            <div class="no-products-state">
                                <i class="fa-solid fa-box-open no-products-icon"></i>
                                <h3 class="no-products-title">Không tìm thấy sản phẩm</h3>
                                <p class="no-products-desc">Vui lòng thử từ khóa khác hoặc <a href="?page=products" class="no-products-link">xem tất cả sản phẩm</a></p>
                            </div>

                        <?php 
                        } else { 
                            // Nếu có sản phẩm: Bắt đầu vòng lặp
                            foreach ($filteredProducts as $p) { 
                                
                                // --- CHUẨN BỊ DỮ LIỆU TRƯỚC KHI IN RA HTML ---
                                
                                // 1. Tên danh mục (Nếu không có thì để mặc định)
                                $catName = isset($p['category_name']) ? $p['category_name'] : 'Chưa phân loại';
                                
                                // 2. Xử lý hiển thị Giá bán và Màu sắc
                                if ($p['price'] == 0) {
                                    $priceText  = 'Liên hệ';
                                    $priceClass = 'contact-price'; // Class làm cho chữ có màu vàng
                                } else {
                                    // Hàm number_format giúp định dạng 1000000 thành 1.000.000
                                    $priceText  = number_format($p['price'], 0, ',', '.') . 'đ';
                                    $priceClass = ''; // Không có class thì giữ màu đỏ mặc định
                                }

                                // 3. Xử lý hiển thị Giá cũ (chỉ tạo biến nếu giá cũ lớn hơn 0)
                                $oldPriceText = '';
                                if ($p['old_price'] > 0) {
                                    $oldPriceText = number_format($p['old_price'], 0, ',', '.') . 'đ';
                                }
                                
                                // ---------------------------------------------
                            ?>
                                <div class="product-card">
                                    
                                    <div class="product-img-wrap">
                                        <img src="<?php echo ROOT_URL ?>uploads/products/images/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy" onerror="this.onerror=null; this.src='<?php echo ROOT_URL ?>uploads/products/images/noimage.jpg';">
                                        
                                        <?php if ($p['stock'] == 0) { ?>
                                            <span class="product-badge badge-out">Hết hàng</span>
                                        <?php } ?>
                                    </div>
                                    
                                    <div class="product-body">
                                        <div class="product-cat"><?= e($catName) ?></div>
                                        <h3 class="product-name" title="<?= e($p['name']) ?>"><?= e($p['name']) ?></h3>
                                        
                                        
                                        <?php if (!empty($p['power_capacity'])) { ?>
                                            <span class="product-power"><i class="fa-solid fa-bolt product-power-icon"></i> <?= e($p['power_capacity']) ?></span>
                                        <?php } ?>

                                        <?php
                                            if(!empty($p['warranty'])){
                                                ?>
                                                    <p class="product-warranty-p">Bảo Hành: <?php echo $p['warranty'] ?></p>
                                                <?php
                                            }
                                            else{

                                            }
                                        ?>
                                        
                                        <div class="product-price-row">
                                            <span class="product-price <?= $priceClass ?>">
                                                <?= $priceText ?>
                                            </span>
                                            
                                            <?php if ($oldPriceText != '') { ?>
                                                <span class="product-old-price"><?= $oldPriceText ?></span>
                                            <?php } ?>
                                        </div>
                                        
                                        <div class="product-actions">
                                            <!-- <a href="?page=detail_product_test&slug=<?= e($p['slug']) ?>" class="btn btn-ghost btn-sm frame-black">Chi tiết</a> -->
                                            <?php 
                                                // Tạo tham số linh hoạt: ưu tiên slug, không có thì dùng id
                                                $params = !empty($p['slug']) ? 'slug=' . e($p['slug']) : 'id=' . $p['id'];
                                            ?>
                                            <a href="?page=detail_product&<?= $params ?>" class="btn btn-ghost btn-sm frame-black">Chi tiết</a>
                                            <?php if ($p['price'] > 0 && $p['stock'] > 0) { ?>
                                                <button class="btn btn-primary btn-sm btn-add-cart" data-id="<?= $p['id'] ?>" data-name="<?= e($p['name']) ?>" data-price="<?= $p['price'] ?>" data-img="<?= ROOT_URL ?>uploads/products/images/<?= e($p['image']) ?>">
                                                    <i class="fa-solid fa-cart-plus"></i>
                                                </button>
                                            <?php } else { ?>
                                                <a href="?page=contact" class="btn btn-amber btn-sm">Liên hệ</a>
                                            <?php } ?>
                                        </div>
                                        
                                    </div>
                                </div>
                            <?php 
                            } // Kết thúc vòng lặp foreach
                        } // Kết thúc if else kiểm tra mảng rỗng
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Bắt trọn khu vực sản phẩm để theo dõi mọi cú click
        const productsSection = document.querySelector('.products-section');

        // Hàm thực hiện tải dữ liệu ngầm (AJAX)
        function fetchFilteredProducts(sourceUrl = null) {
            const grid = document.querySelector('.products-data-grid');
            const countText = document.querySelector('.products-count-text');
            if (grid) grid.style.opacity = '0.4'; // Làm mờ lưới sản phẩm

            // 1. Khởi tạo URL (Từ Link Danh mục HOẶC URL hiện tại)
            const url = new URL(sourceUrl || window.location.href, window.location.origin);

            document.querySelectorAll('.brand-filter-check:checked').forEach(cb => {
                url.searchParams.append('brand[]', cb.value);
            });

            // 2. Đổi lại phần này: Xóa hết các tham số cũ trên URL
            url.searchParams.delete('brand[]'); 
            url.searchParams.delete('power[]'); 
            url.searchParams.delete('price[]'); 
            url.searchParams.delete('type[]'); 

            // Lấy TẤT CẢ checkbox đang tick và đẩy vào URL theo đúng name của nó
            document.querySelectorAll('.brand-filter-check:checked').forEach(cb => {
                url.searchParams.append(cb.name, cb.value); 
            });

            // 3. Đọc lại tùy chọn Sắp xếp và nhét vào URL
            const sortSelect = document.querySelector('.toolbar-sort-select');
            if (sortSelect) {
                url.searchParams.set('sort', sortSelect.value);
            }

            // 4. Gọi AJAX tải dữ liệu
            fetch(url.toString())
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Cập nhật Lưới sản phẩm & Số lượng tìm thấy
                    const newGrid = doc.querySelector('.products-data-grid');
                    const newCountText = doc.querySelector('.products-count-text');
                    
                    if (newGrid && grid) {
                        grid.innerHTML = newGrid.innerHTML;
                        grid.style.opacity = '1';
                    }
                    if (newCountText && countText) {
                        countText.innerHTML = newCountText.innerHTML;
                    }

                    // Cập nhật lại Widget Danh mục (Để bôi đen class 'active' vào đúng danh mục vừa bấm)
                    const oldCatItem = document.querySelector('.cat-list-item');
                    const newCatItem = doc.querySelector('.cat-list-item');
                    if (oldCatItem && newCatItem) {
                        const oldCatPanel = oldCatItem.closest('.widget-panel');
                        const newCatPanel = newCatItem.closest('.widget-panel');
                        if (oldCatPanel && newCatPanel) {
                            oldCatPanel.innerHTML = newCatPanel.innerHTML;
                        }
                    }

                    // Đổi URL trên thanh trình duyệt (Để khách copy gửi cho bạn bè vẫn đúng)
                    window.history.pushState({}, '', url);
                })
                .catch(error => {
                    console.error('Lỗi khi tải dữ liệu lọc:', error);
                    if (grid) grid.style.opacity = '1';
                });
        }

        // --- BẮT SỰ KIỆN TỰ ĐỘNG BẰNG CƠ CHẾ EVENT DELEGATION ---
        if (productsSection) {
            
            // Sự kiện 1: Khi tick ô Thương hiệu hoặc đổi ô Sắp xếp
            productsSection.addEventListener('change', function(e) {
                if (e.target.matches('.brand-filter-check') || e.target.matches('.toolbar-sort-select')) {
                    fetchFilteredProducts();
                }
            });

            // Sự kiện 2: Khi Click vào Link Danh Mục
            productsSection.addEventListener('click', function(e) {
                const catLink = e.target.closest('.cat-list-item'); // Tìm xem có phải bấm vào thẻ <a> danh mục ko
                if (catLink) {
                    e.preventDefault(); // CHẶN LỆNH LOAD TRANG MẶC ĐỊNH CỦA TRÌNH DUYỆT
                    fetchFilteredProducts(catLink.href); // Lấy cái link đó đưa vào tải ngầm AJAX
                }
            });
            
        }

        // Sự kiện 3: Khi click vào nút "Xem thêm" của Danh mục hoặc Thương hiệu
        productsSection.addEventListener('click', function(e) {
            const widgetTitle = e.target.closest('.widget-title');
            if (widgetTitle) {
                const panel = widgetTitle.closest('.widget-panel');
                if (panel) panel.classList.toggle('widget-collapsed');
                
                // Đổi icon +/-
                const icon = widgetTitle.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-minus');
                    icon.classList.toggle('fa-plus');
                }
                return; // Bấm tiêu đề rồi thì thôi không chạy lệnh bên dưới nữa
            }
            
            const catToggleBtn = e.target.closest('.cat-toggle-btn');
            if (catToggleBtn) {
                const panel = catToggleBtn.closest('.widget-panel');
                const container = panel.querySelector('.cat-list-container');

                if (!container) return;

                container.classList.toggle('expanded');

                if (container.classList.contains('expanded')) {
                    catToggleBtn.innerHTML = 'Thu gọn <i class="fa-solid fa-angle-up"></i>';
                } else {
                    catToggleBtn.innerHTML = 'Xem thêm <i class="fa-solid fa-angle-down"></i>';
                }
            }

            const brandToggleBtn = e.target.closest('.brand-toggle-btn');
            if (brandToggleBtn) {
                const panel = brandToggleBtn.closest('.widget-panel');
                const container = panel.querySelector('.brand-list-container');

                if (!container) return;

                container.classList.toggle('expanded');

                if (container.classList.contains('expanded')) {
                    brandToggleBtn.innerHTML = 'Thu gọn <i class="fa-solid fa-angle-up"></i>';
                } else {
                    brandToggleBtn.innerHTML = 'Xem thêm <i class="fa-solid fa-angle-down"></i>';
                }
            }
        });
    });
    
</script>