<?php
    session_start();

// #region agent log
file_put_contents(__DIR__ . '/../../debug-6a4c11.log', json_encode([
    'sessionId' => '6a4c11',
    'runId' => 'initial',
    'hypothesisId' => 'H1',
    'location' => 'public/actions/add-cart.php:4',
    'message' => 'add-cart endpoint hit',
    'data' => [
        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        'phpSessionId' => session_id()
    ],
    'timestamp' => round(microtime(true) * 1000)
], JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
// #endregion

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nhận ID sản phẩm từ Javascript gửi qua
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    // #region agent log
    file_put_contents(__DIR__ . '/../../debug-6a4c11.log', json_encode([
        'sessionId' => '6a4c11',
        'runId' => 'initial',
        'hypothesisId' => 'H2',
        'location' => 'public/actions/add-cart.php:24',
        'message' => 'add-cart payload parsed',
        'data' => [
            'productId' => $product_id,
            'quantity' => $qty
        ],
        'timestamp' => round(microtime(true) * 1000)
    ], JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    // #endregion

    if ($product_id > 0) {
        // 1. Nếu giỏ hàng chưa từng tồn tại, tạo một cái túi trống
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // 2. Nếu món hàng này đã có trong túi -> Cộng dồn số lượng
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $qty;
        } 
        // 3. Nếu món hàng này chưa có -> Thêm mới vào túi
        else {
            $_SESSION['cart'][$product_id] = $qty;
        }

        // Đếm xem trong giỏ đang có tổng cộng bao nhiêu món
        $total_items = array_sum($_SESSION['cart']);
        // #region agent log
        file_put_contents(__DIR__ . '/../../debug-6a4c11.log', json_encode([
            'sessionId' => '6a4c11',
            'runId' => 'initial',
            'hypothesisId' => 'H3',
            'location' => 'public/actions/add-cart.php:45',
            'message' => 'cart updated in session',
            'data' => [
                'phpSessionId' => session_id(),
                'sessionCart' => $_SESSION['cart'],
                'totalItems' => $total_items
            ],
            'timestamp' => round(microtime(true) * 1000)
        ], JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
        // #endregion
        
        // Báo cáo về lại cho Javascript biết là đã xong
        echo json_encode([
            'status' => 'success', 
            'total_items' => $total_items
        ]);
        
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi ID sản phẩm']);
    }
}
?>