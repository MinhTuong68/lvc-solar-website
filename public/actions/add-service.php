<?php
    define('IS_SECURE', true);
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    // Đảm bảo đường dẫn đến file config đúng với cấu trúc của bạn
    include("../../config/constants.php"); 
    include("../../classes/services.php");
    include("../../classes/telegram_helper.php");
    require_once '../../classes/rate_limit.php';
        
    $timeNow = date('d/m/Y - H:i:s');
    $service_obj = new Service($conn);

    // Xử lý khi khách hàng bấm nút XÁC NHẬN ĐẶT LỊCH
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_submit_service'])) {

        if (
            !isset($_POST['csrf_token']) || 
            !isset($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            $_SESSION['toast_message'] = "Yêu cầu không hợp lệ, vui lòng thử lại.";
            $_SESSION['toast_type'] = 'error';
            header("Location: " . BASE_URL . "?page=services");
            exit();
        }

        $spam_guard = new RateLimit('btn_submit_service', 3, 300);
        $check = $spam_guard->check();

        if (!$check['allowed']) {
            // Trả về trang cũ kèm thông báo lỗi thay vì hiện màn hình trắng
            $_SESSION['toast_message'] = $check['message'];
            $_SESSION['toast_type'] = 'error';
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
        
        // 1. Lấy dữ liệu từ form (khớp với thuộc tính name trong file services.php)
        $name         = e($_POST['customer_name'] ?? '');
        $phone        = e($_POST['phone'] ?? '');
        $email = $_POST['email'] ?? '';
        $address      = e($_POST['address'] ?? '');
        $service_type_id = isset($_POST['service_type']) ? (int)$_POST['service_type'] : 0;
        $bill         = e($_POST['electricity_bill'] ?? '');
        $booking_date = e($_POST['booking_date'] ?? '');
        $issue        = e($_POST['issue_description'] ?? '');
        
        
        // Những cột DB có mà form không nhập thì truyền rỗng
        $email = ''; 
        $capacity = '';

        // Tách raw data cho Telegram
        $name_raw  = trim($_POST['customer_name'] ?? '');
        $phone_raw = trim($_POST['phone'] ?? '');
        $address_raw = trim($_POST['address'] ?? '');
        $issue_raw = trim($_POST['issue_description'] ?? '');

        // e() cho DB
        $name  = e($name_raw);
        $phone = e($phone_raw);
        $address = e($address);
        $issue = e($issue_raw);

        // 2. Gọi hàm lưu vào Database
        $result = $service_obj->addService($name, $phone, $email, $address, $service_type_id, $bill, $capacity, $issue, $booking_date);

        // 3. Thông báo Toast giống y hệt trang Home
        if ($result) {
            $_SESSION['toast_message'] = "Đặt lịch thành công! LVC Solar sẽ liên hệ với bạn trong ít phút.";
            $_SESSION['toast_type'] = 'success';
            $all_types = $service_obj->getAllServiceTypes(); 
            $service_name_text = "Dịch vụ khác (ID: " . $service_type_id . ")"; 
            
            if (!empty($all_types)) {
                foreach ($all_types as $type) {
                    if ($type['id'] == $service_type_id) {
                        $service_name_text = $type['service_type']; 
                        break;
                    }
                }
            }
            
            $msgQuote = "<b>📝 CÓ YÊU CẦU KHẢO SÁT / BÁO GIÁ</b>\n";
            $msgQuote .= "⏰ Thời gian: " . $timeNow . "\n";
            $msgQuote .= "Khách hàng: " . $name_raw . "\n";
            $msgQuote .= "SĐT: " . $phone_raw . "\n";
            $msgQuote .= "Địa chỉ: " . $address_raw . "\n";
            $msgQuote .= "Dịch vụ: " . $service_name_text . "\n";
            $msgQuote .= "Tiền điện: " . $bill . "\n";
            if(!empty($booking_date) && $booking_date != 'NULL'){
                $msgQuote .= "Ngày khảo sát: <i>" . $booking_date . "</i>\n";
            } 
            if(!empty($issue_raw)){
                $msgQuote .= "Ghi chú: <i>" . $issue_raw . "</i>\n";
            }
            $telegram = new TelegramHelper();
            $telegram->sendMessage($msgQuote);

        } else {
            $_SESSION['toast_message'] = "Lỗi hệ thống, vui lòng thử lại sau.";
            $_SESSION['toast_type'] = 'error';
        }
        
        // 4. Quay về trang dịch vụ
        header("Location: " . BASE_URL . "?page=services");
        exit();
    }
?>