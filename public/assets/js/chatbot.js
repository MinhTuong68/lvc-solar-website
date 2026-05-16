(function () {
  'use strict';

  const toggler  = document.querySelector('.cb-toggler');
  const closeBtn = document.getElementById('cbClose');
  const tooltip  = document.getElementById('cbTooltip');
  const messages = document.getElementById('cbMessages');
  const input    = document.getElementById('cbInput');
  const sendBtn  = document.getElementById('cbSend');
  const csrf     = document.getElementById('cbCsrfToken');
  const suggBtns = document.querySelectorAll('.cb-suggestion-btn');

  if (!toggler) return;

  let isLoading = false;

const IMG_BASE = ROOT_URL + 'uploads/products/images/';
const IMG_DEFAULT = ROOT_URL + 'uploads/products/images/default.jpg';

  // ── Toggle chatbot ──────────────────────────────────────────────────────────
  toggler.addEventListener('click', () => {
    document.body.classList.toggle('cb-open');
    tooltip.classList.remove('cb-show');
    if (document.body.classList.contains('cb-open')) input.focus();
  });
  closeBtn && closeBtn.addEventListener('click', () => document.body.classList.remove('cb-open'));
  tooltip.addEventListener('click', () => {
    tooltip.classList.remove('cb-show');
    document.body.classList.add('cb-open');
    input.focus();
  });
  setTimeout(() => { if (!document.body.classList.contains('cb-open')) tooltip.classList.add('cb-show'); }, 3000);
  setTimeout(() => tooltip.classList.remove('cb-show'), 11000);

  // ── Quick suggestions ───────────────────────────────────────────────────────
  suggBtns.forEach(btn => btn.addEventListener('click', () => {
    input.value = btn.textContent.replace(/^[^\w\dÀ-ỹ]+/u, '').trim();
    sendMessage();
  }));

  // ── Input auto-resize + Enter gửi ──────────────────────────────────────────
  input.addEventListener('input', () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 120) + 'px';
  });
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
  });
  sendBtn.addEventListener('click', sendMessage);

  // ── Helpers ─────────────────────────────────────────────────────────────────
  function esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function money(n) {
    const num = parseFloat(n);
    if (!num || num <= 0) return 'Liên hệ báo giá';
    return num.toLocaleString('vi-VN') + '₫';
  }
  function scrollBot() {
    requestAnimationFrame(() => messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' }));
  }

  // ── Thêm tin nhắn văn bản ───────────────────────────────────────────────────
  function addMsg(html, type, isRawHtml = false) {
    const li = document.createElement('li');
    li.className = 'cb-msg ' + type;
    const content = isRawHtml ? html : esc(html);
    if (type === 'incoming') {
      li.innerHTML = `<div class="cb-msg-avatar"><span class="material-symbols-rounded">solar_power</span></div><div class="cb-bubble">${content}</div>`;
    } else {
      li.innerHTML = `<div class="cb-bubble">${content}</div>`;
    }
    messages.appendChild(li);
    scrollBot();
    return li;
  }

  // ── Typing dots ─────────────────────────────────────────────────────────────
  function showTyping() {
    const li = document.createElement('li');
    li.className = 'cb-msg incoming cb-typing';
    li.id = 'cbTyping';
    li.innerHTML = `<div class="cb-msg-avatar"><span class="material-symbols-rounded">solar_power</span></div>
      <div class="cb-bubble"><div class="cb-dot"></div><div class="cb-dot"></div><div class="cb-dot"></div></div>`;
    messages.appendChild(li);
    scrollBot();
  }
  function hideTyping() { document.getElementById('cbTyping')?.remove(); }

  // ── Render product cards (CÓ ẢNH + CLICK) ───────────────────────────────────
  function renderProducts(prods) {
    if (!prods || !prods.length) return;

    // Label đầu
    const label = document.createElement('li');
    label.className = 'cb-msg incoming';
    label.innerHTML = `<div class="cb-msg-avatar"><span class="material-symbols-rounded">solar_power</span></div>
      <div class="cb-bubble" style="padding:6px 10px;font-size:12px;color:#64748b;background:#f1f5f9;border:none;">
        ☀️ Tìm thấy <strong>${prods.length}</strong> sản phẩm phù hợp — click để xem chi tiết:
      </div>`;
    messages.appendChild(label);

    // Container cards
    const li = document.createElement('li');
    li.className = 'cb-msg incoming';
    li.style.cssText = 'flex-direction:column; align-items:stretch; padding:0 8px 8px;';

    const grid = document.createElement('div');
    grid.className = 'cb-products-grid';

    prods.forEach(p => {
      const url   = `?page=detail_product&${p.slug ? 'slug=' + encodeURIComponent(p.slug) : 'id=' + p.id}`;
      const imgSrc = (p.image && p.image !== 'default.jpg')
                     ? IMG_BASE + p.image
                     : IMG_DEFAULT;
      const priceText = money(p.price);
      const oldPrice  = parseFloat(p.old_price) > parseFloat(p.price)
                        ? `<span class="cb-old-price">${parseFloat(p.old_price).toLocaleString('vi-VN')}₫</span>` : '';
      const meta = [p.power_capacity ? '⚡ ' + p.power_capacity : '', p.warranty ? '🛡 ' + p.warranty : '']
                    .filter(Boolean).join(' · ');

      const card = document.createElement('a');
      card.className = 'cb-product-card';
      card.href      = url;
      card.title     = p.name;
      // Mở trong tab hiện tại (cùng website)
      card.target    = '_self';

      card.innerHTML = `
        <div class="cb-product-img-wrap">
          <img src="${esc(imgSrc)}" alt="${esc(p.name)}" loading="lazy"
               onerror="this.onerror=null;this.src='${IMG_DEFAULT}'">
          <div class="cb-product-badge">Xem ngay</div>
        </div>
        <div class="cb-product-body">
          <div class="cb-product-name">${esc(p.name)}</div>
          ${meta ? `<div class="cb-product-meta">${esc(meta)}</div>` : ''}
          <div class="cb-product-pricing">
            <span class="cb-product-price ${priceText === 'Liên hệ báo giá' ? 'cb-contact-price' : ''}">${priceText}</span>
            ${oldPrice}
          </div>
        </div>`;

      grid.appendChild(card);
    });

    li.appendChild(grid);
    messages.appendChild(li);
    scrollBot();
  }

  // ── Gửi tin nhắn ────────────────────────────────────────────────────────────
  async function sendMessage() {
    const text = input.value.trim();
    if (!text || isLoading) return;

    isLoading = true;
    sendBtn.disabled = true;
    addMsg(text, 'outgoing');
    input.value = '';
    input.style.height = 'auto';
    showTyping();

    try {
      const res = await fetch('actions/chatbot_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message:    text,
          csrf_token: csrf?.value || ''
        })
      });
      const data = await res.json();
      hideTyping();

      if (data.status === 'ok') {
        if (data.reply)    addMsg(data.reply, 'incoming', true);
        if (data.products?.length) renderProducts(data.products);
      } else {
        addMsg('Xin lỗi, hệ thống đang bận. Thử lại sau hoặc gọi <strong>0945 671 536</strong>!', 'incoming', true);
      }
    } catch (e) {
      hideTyping();
      addMsg('Không thể kết nối. Kiểm tra mạng hoặc gọi <strong>0945 671 536</strong>! 📶', 'incoming', true);
    } finally {
      isLoading = false;
      sendBtn.disabled = false;
      input.focus();
    }
  }
})();
