<?php
// Đảm bảo session đã start
if (session_status() === PHP_SESSION_NONE) session_start();

// Tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- Google Material Symbols -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,500,1,0" />
<link rel="stylesheet" href="assets/css/chatbot.css">

<!-- ── CHATBOT TOGGLER ── -->
<button class="cb-toggler" aria-label="Mở chatbot tư vấn" title="Tư vấn điện mặt trời">
  <svg class="cb-icon-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="#fff">
    <path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/>
    <circle cx="8" cy="11" r="1.2" fill="#f59e0b"/>
    <circle cx="12" cy="11" r="1.2" fill="#f59e0b"/>
    <circle cx="16" cy="11" r="1.2" fill="#f59e0b"/>
  </svg>
  <span class="material-symbols-rounded cb-icon-close">close</span>
</button>

<!-- ── TOOLTIP ── -->
<div class="cb-tooltip" id="cbTooltip">
  ☀️ Tư vấn điện mặt trời miễn phí!
</div>

<!-- ── CHATBOT WINDOW ── -->
<div class="cb-window" id="cbWindow">

  <!-- Header -->
  <div class="cb-header">
    <div class="cb-avatar">
      <span class="material-symbols-rounded">solar_power</span>
    </div>
    <div class="cb-header-info">
      <div class="cb-header-name">LVC Solar AI</div>
      <div class="cb-header-status">
        <div class="cb-status-dot"></div>
        <span>Trợ lý tư vấn năng lượng mặt trời</span>
      </div>
    </div>
    <button class="cb-close-btn" id="cbClose" aria-label="Đóng">
      <span class="material-symbols-rounded">close</span>
    </button>
  </div>

  <!-- Quick Suggestions -->
  <div class="cb-suggestions" id="cbSuggestions">
    <button class="cb-suggestion-btn">💰 Báo giá lắp đặt</button>
    <button class="cb-suggestion-btn">⚡ Tấm pin mặt trời</button>
    <button class="cb-suggestion-btn">🔋 Pin lưu trữ</button>
    <button class="cb-suggestion-btn">🔧 Bảo trì hệ thống</button>
    <button class="cb-suggestion-btn">📍 Địa chỉ LVC Solar</button>
    <button class="cb-suggestion-btn">📅 Đặt lịch khảo sát</button>
  </div>

  <!-- Messages -->
  <ul class="cb-messages" id="cbMessages">
    <li class="cb-msg incoming">
      <div class="cb-msg-avatar">
        <span class="material-symbols-rounded">solar_power</span>
      </div>
      <div class="cb-bubble">
        Xin chào! 👋 Tôi là trợ lý AI của <strong>LVC Solar</strong>.<br><br>
        Tôi có thể giúp bạn:
        <ul>
          <li>Tư vấn lắp đặt điện mặt trời</li>
          <li>Xem sản phẩm & báo giá</li>
          <li>Giải đáp kỹ thuật</li>
          <li>Đặt lịch khảo sát miễn phí</li>
        </ul>
        Bạn cần tư vấn gì ạ?
      </div>
    </li>
  </ul>

  <!-- Input -->
  <div class="cb-input-area">
    <textarea 
      id="cbInput" 
      placeholder="Nhập câu hỏi của bạn..." 
      rows="1"
      maxlength="500"
      autocomplete="off"
      spellcheck="false"
    ></textarea>
    <button class="cb-send-btn" id="cbSend" aria-label="Gửi">
      <span class="material-symbols-rounded">send</span>
    </button>
  </div>

  <div class="cb-footer-note">
    Powered by LVC Solar AI · Hỗ trợ 24/7
  </div>

</div>

<!-- Hidden CSRF -->
<input type="hidden" id="cbCsrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

<script src="assets/js/chatbot.js"></script>
