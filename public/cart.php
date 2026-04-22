<?php
    // Đọc từ Session cho khớp với add-cart.php
    $cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    // #region agent log
    file_put_contents(__DIR__ . '/../debug-6a4c11.log', json_encode([
        'sessionId' => '6a4c11',
        'runId' => 'initial',
        'hypothesisId' => 'H4',
        'location' => 'public/cart.php:4',
        'message' => 'cart page session read',
        'data' => [
            'phpSessionId' => session_id(),
            'sessionCart' => $cartItems,
            'sessionCartCount' => is_array($cartItems) ? count($cartItems) : -1
        ],
        'timestamp' => round(microtime(true) * 1000)
    ], JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    // #endregion
    
    $cartIds = array_keys($cartItems);
    $cartProducts = [];
    $totalAmount = 0;

    // --- 2. TRUY VẤN DATABASE BẰNG MYSQLI ---
    if (!empty($cartIds) && isset($conn)) {
        $safeIds = array_map('intval', $cartIds); // Ép kiểu số nguyên chống Hack
        $idString = implode(',', $safeIds);
        
        $sql = "SELECT id, name, price, image, slug FROM tbl_products WHERE id IN ($idString)";
        $result = $conn->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $cartProducts[] = $row;
            }
            // #region agent log
            file_put_contents(__DIR__ . '/../debug-6a4c11.log', json_encode([
                'sessionId' => '6a4c11',
                'runId' => 'initial',
                'hypothesisId' => 'H5',
                'location' => 'public/cart.php:34',
                'message' => 'cart product query completed',
                'data' => [
                    'cartIds' => $safeIds,
                    'productRows' => count($cartProducts)
                ],
                'timestamp' => round(microtime(true) * 1000)
            ], JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
            // #endregion
        }
    }
?>

<div class="breadcrumb-bar">
    <div class="container">
        <div class="breadcrumb">
            <a href="?page=home">Trang chủ</a><span class="sep">/</span><span class="current">Giỏ hàng</span>
        </div>
    </div>
</div>

<section class="cart-section">
    <div class="container">
        <h1 class="cart-page-title">
            <i class="fa-solid fa-cart-shopping"></i> Giỏ Hàng Của Bạn
        </h1>

        <div class="cart-layout-grid" id="cart-layout">
            <div>
                <div class="cart-left-box">
                    
                    <?php if (empty($cartProducts)): ?>
                        <div id="empty-state" class="cart-empty-state">
                            <i class="fa-solid fa-cart-xmark"></i>
                            <h3>Giỏ hàng trống</h3>
                            <p>Bạn chưa thêm sản phẩm nào vào giỏ hàng.</p>
                            <a href="?page=products" class="btn btn-primary">Xem sản phẩm</a>
                        </div>
                    <?php else: ?>
                        <div id="cart-list">
                            <?php foreach ($cartProducts as $p): 
                                $qty = $cartItems[$p['id']];
                                $subtotal = $p['price'] * $qty;
                                $totalAmount += $subtotal;
                            ?>
                                <div class="cart-item-row-v2" data-id="<?= $p['id'] ?>">
                                    <img class="cart-item-img-v2" src="<?= ROOT_URL ?>uploads/products/images/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                                    
                                    <div class="cart-item-info-v2">
                                        <a href="?page=detail_product_test&slug=<?= e($p['slug']) ?>" class="cart-item-name"><?= e($p['name']) ?></a>
                                        <div class="cart-item-unit-price"><?= number_format($p['price'], 0, ',', '.') ?>₫</div>
                                    </div>
                                    
                                    <div class="cart-qty-wrapper">
                                        <button class="cart-qty-btn btn-minus" data-id="<?= $p['id'] ?>">−</button>
                                        <span class="cart-qty-num"><?= $qty ?></span>
                                        <button class="cart-qty-btn btn-plus" data-id="<?= $p['id'] ?>">+</button>
                                    </div>
                                    
                                    <div class="cart-item-subtotal-v2"><?= number_format($subtotal, 0, ',', '.') ?>₫</div>
                                    
                                    <button class="cart-del-btn" data-id="<?= $p['id'] ?>" title="Xóa sản phẩm">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                </div>
                
                <a href="?page=products" class="cart-continue-link">
                    <i class="fa-solid fa-arrow-left"></i> Tiếp tục mua sắm
                </a>
            </div>

            <div class="cart-summary-box">
                <h3 class="summary-title">Tóm Tắt Đơn Hàng</h3>
                
                <div class="summary-row">
                    <span>Tạm tính</span>
                    <span id="subtotal-val" class="summary-val"><?= number_format($totalAmount, 0, ',', '.') ?>₫</span>
                </div>
                
                <div class="summary-row">
                    <span>Phí vận chuyển</span>
                    <span class="summary-val-free">Miễn phí</span>
                </div>
                
                <div class="summary-row-total">
                    <span>Tổng cộng</span>
                    <span id="total-val" class="summary-val-total"><?= number_format($totalAmount, 0, ',', '.') ?>₫</span>
                </div>
                
                <div class="summary-vat">(Đã bao gồm VAT)</div>
                
                <a href="?page=checkout" id="checkout-btn" class="btn btn-primary btn-block btn-lg btn-checkout" <?= empty($cartProducts) ? 'style="opacity:0.5; pointer-events:none;"' : '' ?>>
                    <i class="fa-solid fa-lock"></i> Tiến hành đặt hàng
                </a>
                
                <p class="summary-secure-text">
                    <i class="fa-solid fa-shield-halved"></i> Thanh toán an toàn & bảo mật
                </p>
                
                <div class="summary-contact-box">
                    <strong>Hoặc liên hệ trực tiếp:</strong><br>
                    <a href="tel:0912345678">📞 0912.345.678</a> để được tư vấn và đặt hàng nhanh hơn.
                </div>
            </div>
        </div>
    </div>
</section>