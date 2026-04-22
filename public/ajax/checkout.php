<?php
session_start();
define('IS_SECURE', true);
require_once("../../config/publics/constants.php");
include_once("../../classes/order.php");

header('Content-Type: application/json');
// #region agent log
file_put_contents(__DIR__ . '/../../debug-404109.log', json_encode([
    'sessionId' => '404109',
    'runId' => 'pre-fix',
    'hypothesisId' => 'H9',
    'location' => 'public/ajax/checkout.php:entry',
    'message' => 'checkout endpoint entry',
    'data' => [
        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        'action' => $_GET['action'] ?? null,
        'phpSessionId' => session_id(),
        'sessionCartCount' => isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? count($_SESSION['cart']) : 0
    ],
    'timestamp' => round(microtime(true) * 1000)
], JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
// #endregion

function getSessionCartItemsWithPrice($conn): array {
    $sessionCart = $_SESSION['cart'] ?? [];
    if (empty($sessionCart) || !is_array($sessionCart)) return ['items' => [], 'total' => 0];

    $ids = array_map('intval', array_keys($sessionCart));
    if (empty($ids)) return ['items' => [], 'total' => 0];

    $idString = implode(',', $ids);
    $sql = "SELECT id, name, price FROM tbl_products WHERE id IN ($idString) AND status = 1";
    $result = $conn->query($sql);

    $dbProducts = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $dbProducts[(int)$row['id']] = $row;
        }
    }
    // #region agent log
    file_put_contents(__DIR__ . '/../../debug-404109.log', json_encode([
        'sessionId' => '404109',
        'runId' => 'pre-fix',
        'hypothesisId' => 'H11',
        'location' => 'public/ajax/checkout.php:getSessionCartItemsWithPrice',
        'message' => 'session ids vs db ids after status filter',
        'data' => [
            'sessionIds' => $ids,
            'dbIds' => array_map('intval', array_keys($dbProducts)),
            'sessionCount' => count($ids),
            'dbCount' => count($dbProducts)
        ],
        'timestamp' => round(microtime(true) * 1000)
    ], JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    // #endregion

    $items = [];
    $total = 0;
    foreach ($sessionCart as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        if ($qty <= 0 || !isset($dbProducts[$pid])) continue;

        $price = (float)$dbProducts[$pid]['price'];
        $lineTotal = $price * $qty;
        $total += $lineTotal;

        $items[] = [
            'id' => $pid,
            'name' => $dbProducts[$pid]['name'],
            'price' => $price,
            'quantity' => $qty
        ];
    }

    return ['items' => $items, 'total' => $total];
}

// GET: trả cart cho trang checkout
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'cart') {
    $cartData = getSessionCartItemsWithPrice($conn);
    // #region agent log
    file_put_contents(__DIR__ . '/../../debug-404109.log', json_encode([
        'sessionId' => '404109',
        'runId' => 'pre-fix',
        'hypothesisId' => 'H10',
        'location' => 'public/ajax/checkout.php:cartResponse',
        'message' => 'checkout cart response built',
        'data' => [
            'sessionCartRaw' => $_SESSION['cart'] ?? [],
            'responseItemsCount' => count($cartData['items']),
            'responseTotal' => $cartData['total']
        ],
        'timestamp' => round(microtime(true) * 1000)
    ], JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    // #endregion
    echo json_encode([
        'success' => true,
        'items' => $cartData['items'],
        'total' => $cartData['total']
    ]);
    exit;
}

// POST: tạo đơn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_obj = new Order($conn);

    // Lấy items + total từ SESSION + DB (không dùng $_POST['items'])
    $cartData = getSessionCartItemsWithPrice($conn);
    $cartItems = $cartData['items'];
    $total_amount = $cartData['total'];

    if (empty($cartItems)) {
        echo json_encode(['success' => false, 'showToast' => 'Giỏ hàng của bạn đang trống!']);
        exit;
    }

    $customerInfo = [
        'order_code'     => 'LVC-' . strtoupper(substr(md5(time()), 0, 6)),
        'name'           => trim($_POST['customer_name'] ?? ''),
        'phone'          => trim($_POST['customer_phone'] ?? ''),
        'email'          => trim($_POST['customer_email'] ?? ''),
        'address'        => trim($_POST['customer_address'] ?? ''),
        'note'           => trim($_POST['note'] ?? ''),
        'payment_method' => trim($_POST['payment_method'] ?? 'cod'),
        'total_amount'   => $total_amount
    ];

    $orderOk = $order_obj->createOrder($customerInfo, $cartItems);
    // #region agent log
    file_put_contents(__DIR__ . '/../../debug-404109.log', json_encode([
        'sessionId' => '404109',
        'runId' => 'pre-fix',
        'hypothesisId' => 'H2',
        'location' => 'public/ajax/checkout.php:postCreateOrder',
        'message' => 'createOrder result',
        'data' => [
            'orderOk' => (bool) $orderOk,
            'cartItemsCount' => count($cartItems),
            'total_amount' => $total_amount
        ],
        'timestamp' => round(microtime(true) * 1000)
    ], JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    // #endregion
    if ($orderOk) {
        // Có thể clear cart sau khi đặt thành công:
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true, 'showToast' => 'Đặt hàng thành công!']);
    } else {
        echo json_encode(['success' => false, 'showToast' => 'Lỗi hệ thống khi lưu đơn hàng!']);
    }
    exit;
}

echo json_encode(['success' => false, 'showToasts' => 'Yêu cầu không hợp lệ!']);
?>