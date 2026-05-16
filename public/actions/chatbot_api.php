<?php
/**
 * LVC Solar — AI Chatbot API  (v2 — fixed)
 * File: public/actions/chatbot_api.php
 */

include_once('../../config/constants.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status'=>'error','reply'=>'Method not allowed']));
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) exit(json_encode(['status'=>'error','reply'=>'Invalid request']));

// ── CSRF ──────────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();
$csrfInput = trim($body['csrf_token'] ?? '');
if (empty($csrfInput) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfInput)) {
    http_response_code(403);
    exit(json_encode(['status'=>'error','reply'=>'Phiên hết hạn, tải lại trang nhé!']));
}

// ── RATE LIMIT 15 msg / 60s ───────────────────────────────────────────────────
$ip  = $_SERVER['REMOTE_ADDR'] ?? 'x';
$key = 'cb_rl_' . md5($ip);
$now = time();
$rl  = $_SESSION[$key] ?? ['c'=>0,'t'=>$now];
if ($now - $rl['t'] > 60) $rl = ['c'=>0,'t'=>$now];
$_SESSION[$key] = ['c'=>++$rl['c'],'t'=>$rl['t']];
if ($rl['c'] > 15) exit(json_encode(['status'=>'error','reply'=>'Bạn nhắn quá nhanh, chờ tí nhé! ⏳']));

// ── VALIDATE ──────────────────────────────────────────────────────────────────
$userMsg = strip_tags(trim($body['message'] ?? ''));
if (!$userMsg || mb_strlen($userMsg) > 500)
    exit(json_encode(['status'=>'error','reply'=>'Tin nhắn không hợp lệ!']));

// ── THÔNG TIN CÔNG TY ─────────────────────────────────────────────────────────
$company  = $conn->query("SELECT * FROM tbl_settings LIMIT 1")->fetch_assoc() ?? [];
$siteName = $company['site_name']  ?? 'LVC Solar';
$hotline  = $company['hotline']    ?? '0945 671 536';
$address  = $company['address']    ?? 'Số 01, Đường Tôn Đức Thắng, TP. Bạc Liêu';

if (preg_match('/khảo sát|đặt lịch|form|tư vấn tại nhà/iu', $userMsg)) {
    $reply = "📝 <strong>Đặt lịch khảo sát & tư vấn tại nhà (Miễn phí)</strong><br><br>
    Kỹ sư của LVC Solar sẽ đến tận nơi để đo đạc mái nhà, kiểm tra hướng nắng và lên giải pháp tối ưu nhất cho gia đình bạn.<br><br>
    👉 <a href='?page=services#booking' style='display: inline-block; padding: 8px 8px; background: #00875a; color: white; border-radius: 20px; text-decoration: none; font-weight: bold; margin-top: 5px; box-shadow: 0 4px 6px rgba(0,135,90,0.2);'>Điền Form Đặt Lịch Tại Đây</a><br><br>
    Hoặc gọi trực tiếp Hotline: <strong>{$hotline}</strong> để được xếp lịch ngay!";

    exit(json_encode([
        'status'   => 'ok',
        'reply'    => $reply,
        'products' => []
    ], JSON_UNESCAPED_UNICODE));
}

// ── LẤY DANH MỤC & THƯƠNG HIỆU TỪ DB (để đưa vào prompt) ────────────────────
function getCatalog($conn) {
    $cats = []; $brands = [];
    $r = $conn->query("SELECT name FROM tbl_categories WHERE status=1 ORDER BY id");
    while ($row = $r->fetch_assoc()) $cats[] = $row['name'];
    $r = $conn->query("SELECT name FROM tbl_brands WHERE status=1 ORDER BY id");
    while ($row = $r->fetch_assoc()) $brands[] = $row['name'];
    return ['cats' => $cats, 'brands' => $brands];
}
$catalog = getCatalog($conn);
$catList   = implode(', ', $catalog['cats'])   ?: 'tấm pin, inverter, pin lưu trữ, đèn năng lượng';
$brandList = implode(', ', $catalog['brands']) ?: 'Panasonic, Gigawatt, LVC';

// ═══════════════════════════════════════════════════════════════════════════════
//  HÀM TÌM SẢN PHẨM — dùng đúng tên cột thật trong DB
// ═══════════════════════════════════════════════════════════════════════════════
function searchProducts($conn, $keyword = '', $limit = 6) {
    if (empty(trim($keyword))) {
        // Không có keyword → trả về sản phẩm nổi bật nhất (bán nhiều nhất + còn hàng)
        $sql = "SELECT p.id, p.name, p.price, p.old_price, p.image, p.slug,
                       p.power_capacity, p.warranty, c.name AS cat_name
                FROM tbl_products p
                LEFT JOIN tbl_categories c ON p.category_id = c.id
                WHERE p.status = 1 AND p.stock > 0
                ORDER BY p.sold DESC, p.created_at DESC
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $limit);
    } else {
        $kw = '%' . $conn->real_escape_string(strip_tags($keyword)) . '%';
        // Tìm theo tên, short_description, content, tên danh mục, tên thương hiệu
        $sql = "SELECT p.id, p.name, p.price, p.old_price, p.image, p.slug,
                       p.power_capacity, p.warranty, c.name AS cat_name
                FROM tbl_products p
                LEFT JOIN tbl_categories c ON p.category_id = c.id
                LEFT JOIN tbl_brands b ON p.brand_id = b.id
                WHERE p.status = 1
                  AND (
                      p.name            LIKE ?
                   OR p.short_description LIKE ?
                   OR p.content         LIKE ?
                   OR c.name            LIKE ?
                   OR b.name            LIKE ?
                  )
                ORDER BY
                    CASE WHEN p.name LIKE ? THEN 0 ELSE 1 END,
                    p.sold DESC
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssi', $kw, $kw, $kw, $kw, $kw, $kw, $limit);
    }
    $stmt->execute();
    $rows = $stmt->get_result();
    $products = [];
    while ($row = $rows->fetch_assoc()) $products[] = $row;
    return $products;
}

// ═══════════════════════════════════════════════════════════════════════════════
//  PHÂN LOẠI Ý ĐỊNH KHÁCH HÀNG
//  Trả về: ['show_products'=>bool, 'keyword'=>string]
// ═══════════════════════════════════════════════════════════════════════════════
function detectIntent($msg) {
    $m = mb_strtolower($msg, 'UTF-8');

    // ① Khách hỏi chung về sản phẩm/hàng hóa → show all featured
    $generalProduct = [
        'sản phẩm', 'có gì', 'bán gì', 'hàng gì', 'xem hàng', 'xem sản phẩm',
        'danh mục', 'catalog', 'có những gì', 'những gì', 'hàng có', 'tư vấn chọn',
        'nên mua gì', 'gợi ý', 'recommend', 'show', 'list', 'liệt kê',
        'có loại nào', 'loại nào', 'mẫu nào', 'dòng nào'
    ];
    foreach ($generalProduct as $kw) {
        if (mb_strpos($m, $kw) !== false) return ['show_products'=>true, 'keyword'=>''];
    }

    // ② Từ khoá sản phẩm cụ thể → tìm theo keyword đó
    $specificMap = [
        // Tấm pin
        'tấm pin'       => 'tấm pin',
        'pin mặt trời'  => 'tấm pin',
        'solar panel'   => 'tấm pin',
        'panel'         => 'tấm pin',
        'aiko'          => 'aiko',
        'panasonic'     => 'panasonic',
        'jinko'         => 'jinko',
        'longi'         => 'longi',
        'gigawatt'      => 'gigawatt',
        // Inverter / biến tần
        'inverter'      => 'inverter',
        'biến tần'      => 'biến tần',
        'huawei'        => 'huawei',
        'solis'         => 'solis',
        'sigenergy'     => 'sigenergy',
        'fronius'       => 'fronius',
        'sma'           => 'sma',
        // Pin lưu trữ
        'pin lưu trữ'   => 'pin lưu trữ',
        'lưu trữ'       => 'pin lưu trữ',
        'byd'           => 'byd',
        'pylontech'     => 'pylontech',
        'battery'       => 'pin lưu trữ',
        'ắc quy'        => 'ắc quy',
        // Đèn
        'đèn năng lượng'=> 'đèn năng lượng',
        'đèn mặt trời'  => 'đèn mặt trời',
        'đèn đường'     => 'đèn đường',
        // Quạt
        'quạt'          => 'quạt năng lượng',
        // Dây / phụ kiện
        'dây điện'      => 'dây điện',
        'phụ kiện'      => '',
        'vật tư'        => '',
        // Gói hệ thống
        'gói'           => 'gói năng lượng',
        'hệ thống'      => '',
        'trọn bộ'       => '',
        'trọn gói'      => '',
    ];
    foreach ($specificMap as $trigger => $searchKw) {
        if (mb_strpos($m, $trigger) !== false) {
            return ['show_products'=>true, 'keyword'=>$searchKw];
        }
    }

    // ③ Có nhắc đến mua / giá / báo giá kèm từ khóa sản phẩm
    if (preg_match('/mua|giá|bao nhiêu|báo giá|order|đặt hàng/u', $m)) {
        // Có ý mua → hiện sản phẩm nổi bật
        return ['show_products'=>true, 'keyword'=>''];
    }

    return ['show_products'=>false, 'keyword'=>''];
}

// ── CHẠY PHÂN LOẠI Ý ĐỊNH ─────────────────────────────────────────────────────
$intent   = detectIntent($userMsg);
$products = [];

if ($intent['show_products']) {
    $products = searchProducts($conn, $intent['keyword'], 6);
    // Nếu tìm keyword không ra → fallback về sản phẩm nổi bật
    if (empty($products) && !empty($intent['keyword'])) {
        $products = searchProducts($conn, '', 6);
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
//  SYSTEM PROMPT — Claude biết danh mục thật + hành vi cụ thể
// ═══════════════════════════════════════════════════════════════════════════════
$productContext = '';
if (!empty($products)) {
    $names = array_column($products, 'name');
    $productContext = "\n\nSẢN PHẨM ĐANG HIỂN THỊ CHO KHÁCH:\n- " . implode("\n- ", $names)
        . "\n(Hệ thống đã hiển thị card sản phẩm có ảnh + link bên dưới. "
        . "Bạn chỉ cần giải thích ngắn gọn, KHÔNG liệt kê lại tên sản phẩm.)";
}

$systemPrompt = <<<SYS
Bạn là trợ lý AI tư vấn của {$siteName} — chuyên lắp đặt điện mặt trời tại Bạc Liêu và miền Tây.

THÔNG TIN CÔNG TY:
- Hotline: {$hotline}
- Địa chỉ: {$address}
- Giờ: Thứ 2 – Thứ 7, 7:30–17:30
- Bảo hành: 25 năm tấm pin · 5 năm inverter · 2 năm thi công
- Khảo sát & báo giá tại nhà MIỄN PHÍ

DANH MỤC SẢN PHẨM THỰC TẾ: {$catList}
THƯƠNG HIỆU ĐANG PHÂN PHỐI: {$brandList}
{$productContext}

QUY TẮC:
1. Tiếng Việt, thân thiện, ngắn gọn (tối đa 120 từ)
2. Dùng HTML đơn giản: <strong>, <br>, <ul><li>
3. Emoji phù hợp: ☀️ ⚡ 🔋 💰 🛡️ 📞
4. Nếu hệ thống đã hiện sản phẩm: chỉ giải thích thêm, không liệt kê lại
5. Cuối câu luôn mời gọi hành động: đặt lịch hoặc gọi hotline
6. KHÔNG bịa thông tin giá cụ thể — mời khảo sát để có báo giá chính xác
SYS;

// ═══════════════════════════════════════════════════════════════════════════════
//  GỌI CLAUDE API
// ═══════════════════════════════════════════════════════════════════════════════
$apiKey = getenv('ANTHROPIC_API_KEY');

if (empty($apiKey)) {
    // Fallback không có API key
    $reply = buildFallbackReply($userMsg, $siteName, $hotline, $address, !empty($products));
    exit(json_encode(['status'=>'ok','reply'=>$reply,'products'=>$products], JSON_UNESCAPED_UNICODE));
}

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_POSTFIELDS     => json_encode([
        'model'      => 'claude-sonnet-4-20250514',
        'max_tokens' => 400,
        'system'     => $systemPrompt,
        'messages'   => [['role'=>'user','content'=>$userMsg]]
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-api-key: '.$apiKey,
        'anthropic-version: 2023-06-01'
    ]
]);

$raw      = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$reply = "Xin lỗi, tôi đang gặp sự cố. Vui lòng gọi <strong>{$hotline}</strong> để được hỗ trợ! 📞";
if ($httpCode === 200 && $raw) {
    $data = json_decode($raw, true);
    if (!empty($data['content'][0]['text'])) $reply = $data['content'][0]['text'];
}

// ── PHẢN HỒI ──────────────────────────────────────────────────────────────────
echo json_encode([
    'status'   => 'ok',
    'reply'    => $reply,
    'products' => $products
], JSON_UNESCAPED_UNICODE);


// ═══════════════════════════════════════════════════════════════════════════════
//  FALLBACK KHI KHÔNG CÓ API KEY
// ═══════════════════════════════════════════════════════════════════════════════
function buildFallbackReply($msg, $siteName, $hotline, $address, $hasProducts) {
    $m = mb_strtolower($msg, 'UTF-8');

    $productNote = $hasProducts
        ? "<br><br>👆 Bạn có thể click vào sản phẩm bên dưới để xem chi tiết!"
        : "";

    if (preg_match('/giá cao|đắt nhất|mắc nhất/iu', $m)) {
        global $conn;
        $sql = "SELECT name, slug, price FROM tbl_products WHERE status = 1 ORDER BY price DESC LIMIT 5";
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $response = "💎 <strong>Top các sản phẩm cao cấp có giá trị cao nhất:</strong><br><br>";
            while ($row = mysqli_fetch_assoc($result)) {
                $price_format = ($row['price'] > 0) ? number_format($row['price'], 0, ',', '.') . '₫' : 'Liên hệ';
                $response .= "- <a href='?page=detail_product&slug=".$row['slug']."' target='_blank'><b>".$row['name']."</b></a> (".$price_format.")<br>";
            }
            return $response;
        }
        return "Hiện tại em chưa tìm thấy sản phẩm nào ạ.";
    }   
    
    if (preg_match('/giá thấp|rẻ nhất|giá rẻ/iu', $m)) {
        global $conn;
        $sql = "SELECT name, slug, price FROM tbl_products WHERE status = 1 AND price > 0 ORDER BY price ASC LIMIT 5";
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $response = "🏷️ <strong>Các sản phẩm có mức giá tốt nhất hiện tại:</strong><br><br>";
            while ($row = mysqli_fetch_assoc($result)) {
                $price_format = number_format($row['price'], 0, ',', '.') . '₫';
                $response .= "- <a href='?page=detail_product&slug=".$row['slug']."' target='_blank'><b>".$row['name']."</b></a> (".$price_format.")<br>";
            }
            return $response;
        }
        return "Hiện tại em chưa tìm thấy sản phẩm nào ạ.";
    }

    if (preg_match('/báo giá|giá|chi phí|bao nhiêu|tiền/u', $m))
        return "💰 Giá lắp đặt phụ thuộc công suất và loại thiết bị.<br><br>
        <strong>Tham khảo:</strong><br>⚡ 3kWp: ~35–50 triệu<br>⚡ 5kWp: ~55–80 triệu<br>⚡ 10kWp: ~100–150 triệu<br><br>
        📞 Gọi <strong>{$hotline}</strong> để được báo giá chính xác miễn phí!{$productNote}";

    if (preg_match('/sản phẩm|có gì|bán gì|xem hàng|danh mục/u', $m))
        return "☀️ <strong>{$siteName}</strong> cung cấp đầy đủ thiết bị điện mặt trời:<br>
        tấm pin, inverter/biến tần, pin lưu trữ, đèn năng lượng, dây điện và phụ kiện.{$productNote}<br><br>
        📞 Tư vấn thêm: <strong>{$hotline}</strong>";

    // if (preg_match('/khảo sát|đặt lịch|tư vấn|hẹn/u', $m))
    //     return "📅 <strong>Khảo sát & báo giá MIỄN PHÍ tại nhà!</strong><br><br>
    //     Kỹ sư đến đo đạc, tư vấn công suất phù hợp.<br>
    //     📞 Đặt lịch ngay: <strong>{$hotline}</strong><br>
    //     📍 {$address}";

    if (preg_match('/địa chỉ|ở đâu|văn phòng/u', $m))
        return "📍 <strong>{$siteName}</strong><br>{$address}<br><br>
        🕐 Thứ 2 – Thứ 7, 7:30–17:30<br>
        📞 <strong>{$hotline}</strong>";

    if (preg_match('/báo giá|giá|chi phí|bao nhiêu|tiền/u', $m))
    return "💰 Giá lắp đặt phụ thuộc công suất và loại thiết bị.<br><br>
    <strong>Tham khảo sơ bộ:</strong><br>
    ⚡ Hệ 3kWp: ~35–50 triệu<br>
    ⚡ Hệ 5kWp: ~55–80 triệu<br>
    ⚡ Hệ 10kWp: ~100–150 triệu<br>
    ⚡ Hệ 20kWp+: Liên hệ báo giá<br><br>
    📞 Gọi <strong>{$hotline}</strong> để được báo giá chính xác miễn phí!{$productNote}";

    if (preg_match('/sản phẩm|có gì|bán gì|xem hàng|danh mục|hàng gì|mua gì/u', $m))
        return "☀️ <strong>{$siteName}</strong> cung cấp đầy đủ thiết bị:<br><br>
        ☀️ Tấm pin năng lượng mặt trời<br>
        ⚙️ Inverter / biến tần<br>
        🔋 Pin lưu trữ điện<br>
        💡 Đèn năng lượng mặt trời<br>
        🌀 Quạt năng lượng mặt trời<br>
        🔌 Dây điện & phụ kiện{$productNote}<br><br>
        📞 Tư vấn thêm: <strong>{$hotline}</strong>";

    if (preg_match('/khảo sát|đặt lịch|tư vấn|hẹn|liên hệ/u', $m))
        return "📅 <strong>Khảo sát & báo giá MIỄN PHÍ tại nhà!</strong><br><br>
        Kỹ sư đến đo đạc, tư vấn công suất phù hợp.<br>
        📞 Đặt lịch ngay: <strong>{$hotline}</strong><br>
        📍 {$address}";

    if (preg_match('/địa chỉ|ở đâu|văn phòng|showroom|trụ sở/u', $m))
        return "📍 <strong>{$siteName}</strong><br>{$address}<br><br>
        🕐 Thứ 2 – Thứ 7, 7:30–17:30<br>
        📞 <strong>{$hotline}</strong>";

    if (preg_match('/hoàn vốn|thu hồi vốn|lời|lãi|tiết kiệm|lợi nhuận/u', $m))
        return "📈 <strong>Thời gian hoàn vốn điện mặt trời:</strong><br><br>
        ✅ Thông thường: <strong>4–7 năm</strong><br>
        ✅ Tiết kiệm: 70–90% tiền điện hàng tháng<br>
        ✅ Tuổi thọ hệ thống: 25–30 năm<br>
        ✅ Có thể bán điện dư lên lưới EVN<br><br>
        💡 Đầu tư 1 lần, dùng điện miễn phí 20+ năm!<br>
        📞 <strong>{$hotline}</strong> để tính toán cụ thể cho nhà bạn.";

    if (preg_match('/mấy kwp|bao nhiêu kwp|công suất|phù hợp|cần lắp|nhà tôi|gia đình|hộ dân/u', $m))
        return "⚡ <strong>Tính công suất phù hợp:</strong><br><br>
        🏠 Nhà nhỏ (dưới 200kWh/tháng): <strong>3–5kWp</strong><br>
        🏠 Nhà trung bình (200–400kWh): <strong>5–8kWp</strong><br>
        🏭 Nhà lớn / kinh doanh (400kWh+): <strong>10kWp trở lên</strong><br><br>
        💡 Quy tắc nhanh: 1kWp ≈ 4–5 kWh/ngày tại miền Tây.<br>
        📞 Gọi <strong>{$hotline}</strong> để được tính toán chính xác miễn phí!";

    if (preg_match('/vay vốn|trả góp|vay|góp|lãi suất|ngân hàng|tài chính/u', $m))
        return "💳 <strong>Hỗ trợ vay vốn lắp điện mặt trời:</strong><br><br>
        ✅ Hỗ trợ vay <strong>0% lãi suất 12 tháng</strong><br>
        ✅ Thủ tục đơn giản, giải ngân nhanh<br>
        ✅ Hợp tác với nhiều ngân hàng uy tín<br><br>
        📞 Liên hệ <strong>{$hotline}</strong> để được tư vấn phương án tài chính phù hợp!";

    if (preg_match('/hòa lưới|bám tải|off.grid|on.grid|độc lập|mất điện|tích trữ/u', $m))
        return "🔌 <strong>3 loại hệ thống điện mặt trời:</strong><br><br>
        ☀️ <strong>Hòa lưới (On-grid):</strong> Bán điện dư cho EVN, không cần pin lưu trữ, giá rẻ nhất<br><br>
        🔋 <strong>Bám tải (Hybrid):</strong> Dùng điện mặt trời trước, dư thì hòa lưới hoặc lưu vào pin<br><br>
        🏝️ <strong>Độc lập (Off-grid):</strong> Không cần lưới điện, phù hợp vùng sâu, vùng xa<br><br>
        📞 Tư vấn chọn loại phù hợp: <strong>{$hotline}</strong>";

    if (preg_match('/mất bao lâu|thời gian lắp|thi công bao lâu|bao nhiêu ngày|mấy ngày/u', $m))
        return "🗓️ <strong>Thời gian thi công lắp đặt:</strong><br><br>
        🏠 Hộ gia đình (3–10kWp): <strong>1–2 ngày</strong><br>
        🏭 Thương mại (10–50kWp): <strong>3–5 ngày</strong><br>
        🏗️ Công nghiệp (50kWp+): <strong>1–2 tuần</strong><br><br>
        ✅ Đội thợ chuyên nghiệp, thi công gọn gàng, bàn giao đúng hẹn.<br>
        📞 <strong>{$hotline}</strong>";

    if (preg_match('/bảo hành|bảo dưỡng|bảo trì|sửa chữa|hỏng|lỗi|sự cố/u', $m))
        return "🛡️ <strong>Chính sách bảo hành LVC Solar:</strong><br><br>
        ☀️ Tấm pin: <strong>25 năm</strong> hiệu suất<br>
        ⚙️ Inverter: <strong>5 năm</strong><br>
        🔧 Thi công lắp đặt: <strong>2 năm</strong><br><br>
        ✅ Hỗ trợ kỹ thuật 24/7<br>
        ✅ Bảo trì định kỳ tận nơi<br>
        ✅ Vệ sinh tấm pin theo lịch<br><br>
        📞 Hotline kỹ thuật: <strong>{$hotline}</strong>";

    if (preg_match('/tấm pin|pin mặt trời|panel|solar panel|aiko|panasonic|jinko|longi|gigawatt/u', $m))
        return "☀️ <strong>Tấm pin mặt trời LVC Solar phân phối:</strong><br><br>
        🔹 <strong>AIKO</strong> — hiệu suất cao nhất hiện nay (23–24%)<br>
        🔹 <strong>Panasonic</strong> — bền, ổn định, thương hiệu Nhật<br>
        🔹 <strong>Gigawatt</strong> — giá tốt, phù hợp hộ gia đình<br>
        🔹 Và nhiều thương hiệu uy tín khác<br><br>
        📞 Tư vấn chọn tấm pin: <strong>{$hotline}</strong>{$productNote}";

    if (preg_match('/inverter|biến tần|huawei|solis|sigenergy|fronius/u', $m))
        return "⚙️ <strong>Inverter (biến tần) đang phân phối:</strong><br><br>
        🔹 <strong>Huawei</strong> — thông minh, kết nối app, giám sát từ xa<br>
        🔹 <strong>Solis</strong> — bền bỉ, giá hợp lý, phổ biến nhất VN<br>
        🔹 <strong>SiGENERGY</strong> — tích hợp pin lưu trữ, hybrid<br>
        🔹 <strong>Fronius</strong> — thương hiệu Áo, chất lượng cao<br><br>
        📞 Tư vấn chọn inverter: <strong>{$hotline}</strong>{$productNote}";

    if (preg_match('/pin lưu trữ|lưu trữ|battery|byd|pylontech|ắc quy|dự phòng/u', $m))
        return "🔋 <strong>Pin lưu trữ năng lượng mặt trời:</strong><br><br>
        🔹 <strong>BYD</strong> — thương hiệu pin số 1 thế giới, an toàn tuyệt đối<br>
        🔹 <strong>Pylontech</strong> — phổ biến, giá tốt, độ bền cao<br>
        🔹 <strong>Gigabox</strong> — giải pháp lưu trữ tích hợp<br><br>
        💡 Dùng điện ban đêm & không bị ảnh hưởng khi mất điện!<br>
        📞 <strong>{$hotline}</strong>{$productNote}";

    if (preg_match('/đèn|đèn đường|đèn sân vườn|đèn pha|đèn năng lượng/u', $m))
        return "💡 <strong>Đèn năng lượng mặt trời LVC Solar:</strong><br><br>
        🔹 Đèn đường năng lượng: 50W, 60W, 100W, 200W<br>
        🔹 Đèn sân vườn trang trí<br>
        🔹 Đèn pha chiếu sáng diện tích lớn<br><br>
        ✅ Không tốn tiền điện · Tự sạc ban ngày · Tự sáng ban đêm<br>
        📞 Xem báo giá: <strong>{$hotline}</strong>{$productNote}";

    if (preg_match('/quạt|quạt năng lượng|quạt mặt trời/u', $m))
        return "🌀 <strong>Quạt năng lượng mặt trời:</strong><br><br>
        ✅ Chạy hoàn toàn bằng điện mặt trời<br>
        ✅ Không tốn tiền điện<br>
        ✅ Phù hợp: kho xưởng, chuồng trại, nhà ở<br><br>
        📞 Xem sản phẩm & báo giá: <strong>{$hotline}</strong>{$productNote}";

    if (preg_match('/khu vực|tỉnh|miền tây|bạc liêu|cần thơ|sóc trăng|cà mau|kiên giang|an giang|đồng tháp|tiền giang|vĩnh long|hậu giang|trà vinh|bến tre|long an/u', $m))
        return "🗺️ <strong>Khu vực phục vụ của LVC Solar:</strong><br><br>
        ✅ TP. Bạc Liêu & toàn tỉnh Bạc Liêu<br>
        ✅ Cần Thơ, Sóc Trăng, Cà Mau<br>
        ✅ Kiên Giang, An Giang, Đồng Tháp<br>
        ✅ Và toàn bộ các tỉnh <strong>miền Tây Nam Bộ</strong><br><br>
        🚗 Đội kỹ thuật lưu động, khảo sát tận nơi miễn phí!<br>
        📞 <strong>{$hotline}</strong>";

    if (preg_match('/dự án|công trình|đã làm|thi công rồi|thực tế|tham khảo|ví dụ/u', $m))
        return "🏗️ <strong>Công trình tiêu biểu LVC Solar đã thi công:</strong><br><br>
        ⚡ Hệ thống 250kW — Doanh nghiệp Bạc Liêu<br>
        ⚡ Hệ thống 850kWp — Khu công nghiệp miền Tây<br>
        🏠 Hàng trăm hộ gia đình khắp miền Tây<br><br>
        📷 Xem thêm tại: <a href='?page=projects'>trang Dự án</a><br>
        📞 <strong>{$hotline}</strong>";

    if (preg_match('/điện dư|bán điện|evn|net metering|feed.in/u', $m))
        return "⚡ <strong>Bán điện dư lên lưới EVN:</strong><br><br>
        ✅ Hệ hòa lưới tự động bán điện dư cho EVN<br>
        ✅ Giá mua điện dư: ~1.678–1.787 VND/kWh<br>
        ✅ Điện kế 2 chiều đo chính xác điện mua/bán<br><br>
        💡 Vừa dùng điện rẻ, vừa có thêm thu nhập từ điện dư!<br>
        📞 Tư vấn thêm: <strong>{$hotline}</strong>";

    if (preg_match('/hotline|số điện thoại|gọi|liên lạc|zalo|facebook/u', $m))
        return "📞 <strong>Liên hệ LVC Solar:</strong><br><br>
        📱 Hotline / Zalo: <strong>{$hotline}</strong><br>
        📧 Email: vanchocomputer@gmail.com<br>
        📘 Facebook: <a href='https://www.facebook.com/diennangluongmattroimientay' target='_blank'>LVC Solar</a><br>
        📍 {$address}<br><br>
        🕐 Thứ 2 – Thứ 7 · 7:30–17:30";

    if (preg_match('/xin chào|chào|hello|hi\b|bắt đầu|giới thiệu/u', $m))
        return "👋 Xin chào! Tôi là trợ lý AI của <strong>{$siteName}</strong>.<br><br>
        Tôi có thể giúp bạn:<br>
        💰 Báo giá lắp đặt · ⚡ Tư vấn tấm pin<br>
        🔋 Pin lưu trữ · ⚙️ Inverter biến tần<br>
        💡 Đèn năng lượng · 📅 Đặt lịch khảo sát<br><br>
        Bạn cần tư vấn gì ạ?";

    if (preg_match('/cảm ơn|thanks|thank|tuyệt|ok|được rồi/u', $m))
        return "😊 Không có gì! Rất vui được hỗ trợ bạn.<br><br>
        Nếu cần thêm thông tin, đừng ngại hỏi nhé!<br>
        📞 <strong>{$hotline}</strong> — LVC Solar luôn sẵn sàng! ☀️";

    if (preg_match('/so sánh|khác nhau|nên chọn|tốt hơn|loại nào|dòng nào/u', $m))
        return "🤔 <strong>Cách chọn hệ thống điện mặt trời:</strong><br><br>
        📊 <strong>Hòa lưới:</strong> Giá rẻ, hoàn vốn nhanh, phù hợp đa số<br>
        🔋 <strong>Hybrid:</strong> Có pin dự phòng, dùng khi mất điện được<br>
        🏝️ <strong>Off-grid:</strong> Hoàn toàn độc lập, chi phí cao hơn<br><br>
        💡 Lựa chọn phụ thuộc nhu cầu sử dụng và ngân sách.<br>
        📞 Gọi <strong>{$hotline}</strong> để được tư vấn chọn loại phù hợp nhất!";

    if (preg_match('/an toàn|nguy hiểm|cháy nổ|sét|mưa|ngập|rủi ro|độ bền/u', $m))
        return "🛡️ <strong>Hệ thống điện mặt trời có an toàn không?</strong><br><br>
        ✅ Tấm pin chịu được mưa, gió, bão cấp 12<br>
        ✅ Có hệ thống chống sét lan truyền<br>
        ✅ Inverter có bảo vệ quá áp, quá tải, ngắn mạch<br>
        ✅ Đạt chuẩn IEC, UL quốc tế<br>
        ✅ Thi công đúng kỹ thuật, tiếp địa an toàn<br><br>
        📞 Tư vấn kỹ hơn: <strong>{$hotline}</strong>";

    if (preg_match('/mái tôn|mái ngói|mái bằng|mái thái|loại mái|diện tích mái/u', $m))
        return "🏠 <strong>Lắp điện mặt trời được trên loại mái nào?</strong><br><br>
        ✅ Mái tôn (phổ biến nhất)<br>
        ✅ Mái bằng bê tông<br>
        ✅ Mái ngói (cần khảo sát độ dốc)<br>
        ✅ Mái thái, mái nhật<br><br>
        📐 1kWp cần khoảng 5–7m² diện tích mái.<br>
        📞 Kỹ sư khảo sát miễn phí: <strong>{$hotline}</strong>";
    
    if (preg_match('/khảo sát|đặt lịch|form|tư vấn tại nhà/iu', $m)) {
        return "📝 <strong>Đặt lịch khảo sát & tư vấn tại nhà (Miễn phí)</strong><br><br>
        Kỹ sư của LVC Solar sẽ đến tận nơi để đo đạc mái nhà, kiểm tra hướng nắng và lên giải pháp tối ưu nhất cho gia đình bạn.<br><br>
        👉 <a href='?page=contact' style='display: inline-block; padding: 8px 16px; background: #00875a; color: white; border-radius: 20px; text-decoration: none; font-weight: bold; margin-top: 5px; box-shadow: 0 4px 6px rgba(0,135,90,0.2);'>Điền Form Đặt Lịch Tại Đây</a><br><br>
        Hoặc gọi trực tiếp Hotline: <strong>" . (isset($hotline) ? $hotline : '0945 671 536') . "</strong> để được xếp lịch ngay!";
    }


    if (preg_match('/giao hàng|ship|vận chuyển/iu', $m)) {
        return "🚚 <strong>Chính sách giao hàng:</strong><br><br>
        LVC Solar hỗ trợ **giao hàng và thi công trên toàn quốc**.<br>
        - Nội thành: Giao trong 24h.<br>
        - Tỉnh thành khác: 2-5 ngày tùy khu vực.<br><br>
        Về phí vận chuyển sẽ tùy thuộc vào khối lượng đơn hàng, anh/chị để lại SĐT để bên em báo giá chính xác nhé!";
    }
    
    if (preg_match('/trả góp/iu', $m)) {
        return "💳 <strong>Hỗ trợ trả góp:</strong><br><br>
        Dạ bên em CÓ hỗ trợ trả góp qua thẻ tín dụng và các công ty tài chính với thủ tục cực kỳ đơn giản và duyệt hồ sơ nhanh chóng ạ.";
    }

    if (preg_match('/thanh toán|momo|chuyển khoản/iu', $m)) {
        return "💵 <strong>Hình thức thanh toán:</strong><br><br>
        Anh/chị có thể thanh toán linh hoạt bằng nhiều hình thức: Tiền mặt, Chuyển khoản ngân hàng, hoặc quét mã Momo/VNPay ạ.";
    }

    if (preg_match('/đặt hàng|mua như thế nào/iu', $m)) {
        return "🛒 <strong>Cách thức đặt hàng:</strong><br><br>
        Để đặt hàng, anh/chị có thể bấm Thêm vào giỏ hàng trực tiếp trên website, hoặc gọi ngay Hotline **{$hotline}** để được tạo đơn nhanh nhất ạ.";
    }

    if (preg_match('/có tốt không|đánh giá/iu', $m)) {
        return "⭐ <strong>Chất lượng sản phẩm:</strong><br><br>
        Sản phẩm của LVC Solar đều là hàng nhập khẩu chính hãng (Canadian, Growatt, Huawei...), có đầy đủ giấy tờ CO/CQ. Rất nhiều khách hàng và đối tác doanh nghiệp đã lắp đặt và đánh giá hiệu suất cực kỳ ổn định ạ.";
    }

    // =================================================================
    // 3. NHÓM TƯ VẤN ĐIỆN MẶT TRỜI CHUNG
    // =================================================================

    if (preg_match('/nhà tôi|bao nhiêu tấm|5kw|phù hợp/iu', $m)) {
        return "📐 <strong>Tư vấn công suất:</strong><br><br>
        Để tính được số tấm pin và công suất phù hợp (ví dụ hệ 5kW), em cần biết hóa đơn tiền điện trung bình tháng của nhà mình là bao nhiêu ạ?<br><br>
        Hệ 5kW thường cần khoảng 9-11 tấm pin và tạo ra 20-25 ký điện mỗi ngày, đủ dùng cho điều hòa, tủ lạnh, tivi...";
    }

    if (preg_match('/hết bao nhiêu tiền|hoàn vốn|chi phí/iu', $m)) {
        return "💰 <strong>Chi phí & Hoàn vốn:</strong><br><br>
        Chi phí lắp đặt phụ thuộc vào công suất hệ thống (thường từ 12-15 triệu/1kWp).<br><br>
        Tuy nhiên, điện mặt trời là khoản đầu tư sinh lời, thời gian thu hồi vốn hiện tại rất nhanh, chỉ mất khoảng **4 đến 5 năm** là anh/chị đã dùng điện hoàn toàn miễn phí!";
    }

    if (preg_match('/ban đêm|trời mưa/iu', $m)) {
        return "🌙 <strong>Hoạt động ban đêm & trời mưa:</strong><br><br>
        Dạ, ban đêm không có nắng thì tấm pin không tạo ra điện. Lúc này nhà mình sẽ dùng điện lưới (EVN) hoặc điện từ Ắc quy lưu trữ.<br><br>
        Khi trời mưa hoặc nhiều mây, hệ thống vẫn tạo ra điện nhưng công suất sẽ giảm so với trời nắng gắt ạ.";
    }

    // =================================================================
    // 4. NHÓM CÂU HỎI VỀ TẤM PIN & INVERTER/ẮC QUY
    // =================================================================

    if (preg_match('/mono|poly|tấm pin loại nào tốt/iu', $m)) {
        return "☀️ <strong>Loại tấm pin tốt nhất:</strong><br><br>
        Hiện tại tấm pin **Mono (đơn tinh thể)** đang được ưa chuộng và tốt nhất vì hiệu suất chuyển đổi cao hơn, hoạt động tốt ngay cả khi ánh sáng yếu. Bên em chuyên cung cấp các dòng Mono cao cấp ạ.";
    }

    if (preg_match('/hòa lưới|độc lập/iu', $m)) {
        return "⚙️ <strong>Phân biệt hệ thống:</strong><br><br>
        - **Hòa lưới (On-grid):** Cần có điện lưới mới chạy, mất điện là hệ thống ngừng (an toàn lưới điện).<br>
        - **Hybrid (Có lưu trữ):** Kết hợp ắc quy, khi MẤT ĐIỆN lưới nhà mình vẫn có điện để dùng bình thường ạ.<br>
        - **Độc lập (Off-grid):** Tách biệt hoàn toàn lưới điện, dùng cho vùng sâu vùng xa.";
    }

    if (preg_match('/lithium/iu', $m)) {
        return "🔋 <strong>Pin lưu trữ Lithium:</strong><br><br>
        Bên em khuyến khích sử dụng **Pin lưu trữ Lithium** thay vì ắc quy viễn thông cũ. Pin Lithium sạc/xả sâu hơn, an toàn hơn và tuổi thọ cực cao (hơn 10 năm).";
    }

    if (preg_match('/điện thoại|online|app/iu', $m)) {
        return "📱 <strong>Giám sát qua điện thoại:</strong><br><br>
        Dạ có! Tất cả Inverter bên em đều tích hợp Wifi. Anh/chị có thể tải app về điện thoại để theo dõi nhà mình hôm nay tạo được bao nhiêu số điện ở bất cứ đâu.";
    }

    if (preg_match('/xin phép điện lực|giấy phép/iu', $m)) {
        return "📝 <strong>Thủ tục điện lực:</strong><br><br>
        Nếu lắp hệ bám tải (không phát ngược điện lên lưới) thì thủ tục rất đơn giản. LVC Solar sẽ hỗ trợ trọn gói các thủ tục giấy tờ, thông báo với điện lực địa phương cho anh/chị yên tâm sử dụng.";
    }
    

    // Mặc định
    return "Cảm ơn bạn đã liên hệ <strong>{$siteName}</strong>! ☀️<br><br>
    📞 Hotline: <strong>{$hotline}</strong><br>
    📍 {$address}{$productNote}";
}
