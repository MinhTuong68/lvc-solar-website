<?php
    define('IS_SECURE', true);
    include("../../config/constants.php"); 
    include("../../classes/contact.php");
    require_once '../../classes/rate_limit.php';
    include("../../classes/telegram_helper.php");
    $contact_obj = new Contact($conn);
    $timeNow = date('d/m/Y - H:i:s');


    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_send_contact'])) {

        if (
            !isset($_POST['csrf_token']) || 
            !isset($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            $_SESSION['toast_message'] = "Yêu cầu không hợp lệ, vui lòng thử lại.";
            $_SESSION['toast_type'] = 'error';
            header("Location: " . BASE_URL . "?page=contact");
            exit();
        }

        // Nút giỏ hàng: Bấm 10 lần khóa 100 giây
        $spam_guard = new RateLimit('btn_send_contact', 3, 300);
        $check = $spam_guard->check();

        if (!$check['allowed']) {
            $_SESSION['toast_message'] = $check['message'];
            $_SESSION['toast_type'] = 'error';
            header("Location: " . $_SERVER['HTTP_REFERER'] . "#contact");
            exit();
        }

        $name    = e($_POST['fullname'] ?? '');
        $phone   = e($_POST['phone'] ?? '');
        $email   = e($_POST['email'] ?? '');
        $subject = e($_POST['subject'] ?? 'Liên hệ chung');
        $message = e($_POST['message'] ?? '');

        $name_raw  = trim($_POST['fullname'] ?? '');
        $phone_raw = trim($_POST['phone'] ?? '');
        $message_raw = trim($_POST['message'] ?? '');

        $name  = e($name_raw);
        $phone = e($phone_raw);
        $message = e($message_raw);

        if ($contact_obj->addContact($name, $phone, $email, $subject, $message)) {
            $_SESSION['toast_message'] = "Gửi tin nhắn thành công! Chúng tôi sẽ phản hồi sớm nhất.";
            $_SESSION['toast_type'] = 'success';

            $msgTelegram = "<b>📩 CÓ TIN NHẮN LIÊN HỆ MỚI</b>\n";
            $msgTelegram .= "⏰ Thời gian: " . $timeNow . "\n";
            $msgTelegram .= "👤 Khách: " . $name_raw . "\n";
            $msgTelegram .= "📞 SĐT: " . $phone_raw . "\n";
            if (!empty($email_raw)) {
                $msgTelegram .= "📧 Email: " . $email . "\n";
            }
            $msgTelegram .= "📌 Chủ đề: " . $subject . "\n";
            $msgTelegram .= "💬 Nội dung: <i>" . $message_raw . "</i>\n";

            $telegram = new TelegramHelper();
            $telegram->sendMessage($msgTelegram);
        } else {
            $_SESSION['toast_message'] = "Lỗi hệ thống, vui lòng thử lại sau.";
            $_SESSION['toast_type'] = 'error';
        }
        
        header("Location: " . BASE_URL . "?page=contact");
        exit();
    }
?>