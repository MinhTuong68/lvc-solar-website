document.addEventListener('DOMContentLoaded', async function () {
    // #region agent log
    fetch('http://127.0.0.1:7349/ingest/2b7fb723-2546-43fa-a3a0-fca4225729f2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'6a4c11'},body:JSON.stringify({sessionId:'6a4c11',runId:'initial-2',hypothesisId:'H6',location:'public/assets/js/checkout.js:2',message:'checkout.js loaded',data:{href:window.location.href},timestamp:Date.now()})}).catch(()=>{});
    // #endregion
    const emptyWrap = document.getElementById('checkout-empty');
    const formWrap = document.getElementById('checkout-form-wrap');
    const itemsList = document.getElementById('checkout-items-list');
    const subtotalEl = document.getElementById('checkout-subtotal');
    const totalEl = document.getElementById('checkout-total');
    const orderError = document.getElementById('order-error');
    let currentItems = [];
    async function getCartFromSession() {
        const res = await fetch('ajax/checkout.php?action=cart', { credentials: 'same-origin' });
        return res.json();
    }

    try {
        const payload = await getCartFromSession();

        if (!payload.success || !Array.isArray(payload.items) || payload.items.length === 0) {
            emptyWrap.style.display = 'block';
            formWrap.style.display = 'none';
            return;
        }
        currentItems = payload.items;

        emptyWrap.style.display = 'none';
        formWrap.style.display = 'grid';

        let html = '';
        payload.items.forEach(item => {
            const itemTotal = Number(item.price) * Number(item.quantity);
            const priceFmt = new Intl.NumberFormat('vi-VN').format(itemTotal) + ' ₫';
            html += `
                <div class="co-item">
                    <div class="co-item-name">
                        ${item.name} <strong style="color:var(--green)">x ${item.quantity}</strong>
                    </div>
                    <div class="co-item-price">${priceFmt}</div>
                </div>
            `;
        });

        itemsList.innerHTML = html;
        const totalFmt = new Intl.NumberFormat('vi-VN').format(payload.total) + ' ₫';
        subtotalEl.innerText = totalFmt;
        totalEl.innerText = totalFmt;
    } catch (e) {
        // #region agent log
        fetch('http://127.0.0.1:7349/ingest/2b7fb723-2546-43fa-a3a0-fca4225729f2',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'6a4c11'},body:JSON.stringify({sessionId:'6a4c11',runId:'initial-2',hypothesisId:'H8',location:'public/assets/js/checkout.js:49',message:'checkout fetch failed',data:{error:String(e)},timestamp:Date.now()})}).catch(()=>{});
        // #endregion
        emptyWrap.style.display = 'none';
        formWrap.style.display = 'none';
    }
    const btnSubmit = document.getElementById('btn-submit-order');
    if (btnSubmit) {
        btnSubmit.addEventListener('click', async function(e){
            e.preventDefault(); 

            const name = (document.getElementById('c_name') || document.getElementById('co-name'))?.value.trim();
            const phone = (document.getElementById('c_phone') || document.getElementById('co-phone'))?.value.trim();
            const address = (document.getElementById('c_address') || document.getElementById('co-address'))?.value.trim();
            
            const emailEl = document.getElementById('c_email') || document.getElementById('co-email');
            const email = emailEl ? emailEl.value.trim() : '';
            
            const noteEl = document.getElementById('c_note') || document.getElementById('co-note');
            const note = noteEl ? noteEl.value.trim() : '';
            
            const paymentEl = document.querySelector('input[name="payment_method"]:checked') || document.querySelector('input[name="payment"]:checked');
            const payment = paymentEl ? paymentEl.value : 'cod';

            if (!name || !phone || !address) {
                showToast('Vui lòng điền đầy đủ: Họ tên, Số điện thoại và Địa chỉ!', 'error');
                return;
            }
            openModal(
                'Xác nhận đặt hàng', 
                'Bạn có chắc chắn muốn chốt đơn hàng này không?', 
                'fa-solid fa-cart-arrow-down', 
                'Chốt đơn', 
                async function() {
                    showLoading('Đang lên đơn, bạn đợi xíu nha...');
                    btnSubmit.disabled = true;
                    const originalText = btnSubmit.innerHTML;
                    btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';

                    const formData = new FormData();
                    formData.append('customer_name', name);
                    formData.append('customer_phone', phone);
                    formData.append('customer_email', email);
                    formData.append('customer_address', address);
                    formData.append('note', note);
                    formData.append('payment_method', payment);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    formData.append('csrf_token', csrfToken);
                    try {
                        const response = await fetch('ajax/checkout.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            sessionStorage.setItem('pendingToast', result.showToast);
                            sessionStorage.setItem('pendingToastType', 'success');
                            
                            // 3. Về trang chủ (Sương mù sẽ biến mất khi trang mới tải xong)
                            window.location.href = '?page=home';
                        } else {
                            hideLoading();
                            showToast(result.showToast, 'error');
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = originalText;
                        }
                    } catch (error) {
                        console.error("Lỗi:", error);
                        hideLoading();
                        showToast('Có lỗi hệ thống mạng xảy ra, vui lòng thử lại sau!', 'error');
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalText;
                    }
                }
            );
        });
    }
});