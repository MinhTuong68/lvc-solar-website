<?php
define('IS_SECURE', true);
require_once("../../config/constants.php");
include_once("../../classes/order.php");
include("../../classes/telegram_helper.php");
require_once '../../classes/rate_limit.php'; 
$timeNow = date('d/m/Y - H:i:s');

header('Content-Type: application/json');

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
    echo json_encode([
        'success' => true,
        'items' => $cartData['items'],
        'total' => $cartData['total']
    ]);
    exit;
}

// POST: tạo đơn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'showToast' => 'Yêu cầu không hợp lệ!']);
        exit;
    }

    $spam_guard = new RateLimit('checkout_order', 5, 300);
    $check = $spam_guard->check();
    if (!$check['allowed']) {
        echo json_encode(['success' => false, 'showToast' => $check['message']]);
        exit;
    }

    $order_obj = new Order($conn);

    // Lấy items + total từ SESSION + DB (không dùng $_POST['items'])
    $cartData = getSessionCartItemsWithPrice($conn);
    $cartItems = $cartData['items'];
    $total_amount = $cartData['total'];

    if (empty($cartItems)) {
        echo json_encode(['success' => false, 'showToast' => 'Giỏ hàng của bạn đang trống!']);
        exit;
    }

    $name_raw    = trim($_POST['customer_name'] ?? '');
    $phone_raw   = trim($_POST['customer_phone'] ?? '');
    $email_raw   = trim($_POST['customer_email'] ?? '');
    $address_raw = trim($_POST['customer_address'] ?? '');
    $note_raw    = strip_tags(trim($_POST['note'] ?? ''));

    $name  = e($name_raw);
    $phone = $phone_raw;
    $allowed_payments = ['cod', 'bank_transfer', 'momo'];
    $payment = trim($_POST['payment_method'] ?? 'cod');
    $payment = in_array($payment, $allowed_payments) ? $payment : 'cod';
    if (empty($name) || empty($phone)) {
        echo json_encode(['success' => false, 'showToast' => 'Vui lòng nhập đầy đủ họ tên và số điện thoại!']);
        exit;
    }

    // Validate SĐT Việt Nam
    if (!preg_match('/^(0|\+84)[0-9]{9}$/', $phone)) {
        echo json_encode(['success' => false, 'showToast' => 'Số điện thoại không hợp lệ!']);
        exit;
    }

    $customerInfo = [
        'order_code'     => 'LVC-' . strtoupper(substr(md5(time()), 0, 6)),
        'name'           => $name,
        'phone'          => $phone,
        'email'   => e(trim($_POST['customer_email'] ?? '')),
        'address' => e(trim($_POST['customer_address'] ?? '')),
        'note'    => e(trim($_POST['note'] ?? '')),
        'payment_method' => $payment,
        'total_amount'   => $total_amount
    ];

    $orderOk = $order_obj->createOrder($customerInfo, $cartItems);
    if ($orderOk) {
        $msgTelegram = "<b>🛒 TING TING! CÓ ĐƠN ĐẶT HÀNG MỚI</b>\n";
        $msgTelegram .= "📦 Mã đơn hàng: <b>" . $customerInfo['order_code'] . "</b>\n";
        $msgTelegram .= "⏰ Thời gian: " . $timeNow . "\n";
        $msgTelegram .= "👤 Khách hàng: " . $name_raw . "\n";
        $msgTelegram .= "📞 SĐT: " . $phone_raw . "\n";
        $msgTelegram .= "📍 Địa chỉ: " . $address_raw . "\n";
        
        $phuong_thuc = ($payment == 'bank_transfer') ? 'Chuyển khoản' : (($payment == 'momo') ? 'Ví Momo' : 'COD (Tiền mặt)');
        $msgTelegram .= "💳 Thanh toán: " . $phuong_thuc . "\n";
        $msgTelegram .= "💰 Tổng tiền: <b>" . number_format($total_amount, 0, ',', '.') . " VNĐ</b>\n";
        
        if (!empty($note_raw)) {
            $msgTelegram .= "📝 Ghi chú: <i>" . $note_raw . "</i>\n";
        }
        
        $telegram = new TelegramHelper();
        $telegram->sendMessage($msgTelegram);

        
        $cus_phone = $phone;
        // ✅ CHỈ DÙNG SESSION
        $auth_phones = $_SESSION['authorized_phones'] ?? [];
        if ($cus_phone !== '' && !in_array($cus_phone, $auth_phones)) {
            $auth_phones[] = $cus_phone;
        }
        $_SESSION['authorized_phones'] = $auth_phones;

        if (!isset($_SESSION['new_orders'])) {
            $_SESSION['new_orders'] = 1;
        } else {
            $_SESSION['new_orders'] += 1;
        }

        $_SESSION['cart'] = [];
        $_SESSION['cart_details'] = [];
        echo json_encode(['success' => true, 'showToast' => 'Đặt hàng thành công!']);
    } else {
        echo json_encode(['success' => false, 'showToast' => 'Lỗi hệ thống khi lưu đơn hàng!']);
    }
    exit;
}

echo json_encode(['success' => false, 'showToasts' => 'Yêu cầu không hợp lệ!']);
?>