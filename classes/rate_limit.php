<?php
class RateLimit {
    private $action_name;
    private $limit;
    private $timeout;

    public function __construct($action_name, $limit = 10, $timeout = 100) {
        $this->action_name = $action_name;
        $this->limit = $limit;
        $this->timeout = $timeout;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function check() {
        $count_key = 'rl_count_' . $this->action_name;
        $time_key  = 'rl_time_' . $this->action_name;
        $now = time();

        // 1. Kiểm tra xem có đang bị khóa không
        if (isset($_SESSION[$time_key]) && $now < $_SESSION[$time_key]) {
            $wait = $_SESSION[$time_key] - $now;
            return ['allowed' => false, 'message' => "Spam à nha! Vui lòng đợi $wait giây nữa."];
        }

        // 2. Nếu đã hết thời gian phạt -> Mở khóa
        if (isset($_SESSION[$time_key]) && $now >= $_SESSION[$time_key]) {
            unset($_SESSION[$time_key]);
            $_SESSION[$count_key] = 0;
        }

        // 3. Tăng số lần thao tác
        $_SESSION[$count_key] = ($_SESSION[$count_key] ?? 0) + 1;

        // 4. Vượt quá giới hạn -> Đưa vào danh sách khóa
        if ($_SESSION[$count_key] >= $this->limit) {
            $_SESSION[$time_key] = $now + $this->timeout;
            return ['allowed' => false, 'message' => "Bạn đã bấm quá {$this->limit} lần! Tạm khóa $this->timeout giây."];
        }

        // 5. Hợp lệ
        return ['allowed' => true];
    }
}
?>