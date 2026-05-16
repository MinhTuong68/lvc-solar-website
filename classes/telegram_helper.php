<?php
    class TelegramHelper {
        private $botToken;
        private $chatId;
        public function __construct() {
            $this->botToken = getenv('TELEGRAM_BOT_TOKEN');
            $this->chatId   = getenv('TELEGRAM_CHAT_ID');
        }
        
        /**
         * Hàm gửi tin nhắn qua Telegram
         * @param string $message
         * @return bool 
         */
        public function sendMessage($message) {
            $url = "https://api.telegram.org/bot" . $this->botToken . "/sendMessage";
            
            $data = [
                'chat_id'    => $this->chatId,
                'text'       => $message,
                'parse_mode' => 'HTML'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Quá 10s không gửi được thì bỏ qua, không làm treo web
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return ($httpCode == 200);
        }
    }
?>