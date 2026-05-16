<?php
    define('IS_SECURE', true);
    include("../../config/constants.php");
    include("../../classes/services.php");
    include("../../classes/telegram_helper.php");
    require_once '../../classes/rate_limit.php';
    $timeNow = date('d/m/Y - H:i:s');
    $service_obj = new Service($conn);
    

    // 2. Xử lý khi khách hàng bấm nút GỬI YÊU CẦU
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_submit_quote'])) {
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

        $spam_guard = new RateLimit('btn_submit_quote', 3, 300);
        $check = $spam_guard->check();

        if (!$check['allowed']) {
            // Trả về trang cũ kèm thông báo lỗi thay vì hiện màn hình trắng
            $_SESSION['toast_message'] = $check['message'];
            $_SESSION['toast_type'] = 'error';
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
        
        $name    = e($_POST['customer_name'] ?? '');
        $phone   = e($_POST['phone'] ?? '');
        $address = e($_POST['address'] ?? '');
        $bill    = e($_POST['electricity_bill'] ?? '');
        $issue   = e($_POST['issue_description'] ?? '');
        $service_type_id = isset($_POST['service_type_id']) ? (int)$_POST['service_type_id'] : 0;

        // Những cột DB có mà form không có (Email, Dung lượng, Ngày hẹn) thì truyền chuỗi rỗng
        $email = ''; 
        $capacity = '';
        $booking_date = '';

        $name_raw  = trim($_POST['customer_name'] ?? '');
        $phone_raw = trim($_POST['phone'] ?? '');
        $address_raw = trim($_POST['address'] ?? '');
        $issue_raw = trim($_POST['issue_description'] ?? '');

        // e() cho DB
        $name  = e($name_raw);
        $phone = e($phone_raw);
        $address = e($address);
        $issue = e($issue_raw);

        // Gọi hàm AddService để lưu vào CSDL
        $result = $service_obj->addService($name, $phone, $email, $address, $service_type_id, $bill, $capacity, $issue, $booking_date);

        // Thông báo kết quả
        if ($result) {
            $_SESSION['toast_message'] = "Gửi yêu cầu thành công! LVC Solar sẽ liên hệ ngay.";
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
        
        // Tránh tình trạng khách F5 bị gửi lại form 2 lần
        header("Location: " . BASE_URL . "?page=home");
        exit();
    }

    // 3. Lấy danh sách loại dịch vụ để tự động in ra form
    $allTypes = $service_obj->getAllServiceTypes();
?>