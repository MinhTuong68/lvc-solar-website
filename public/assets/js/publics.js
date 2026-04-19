function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    // 1. Tạo thẻ div chứa thông báo và gắn class theo type
    const toast = document.createElement('div');
    toast.className = `toast-msg ${type}`; // Sẽ tạo ra class "toast-msg success" hoặc "toast-msg error"

    // 2. Quyết định icon: Nếu type là error thì lấy icon dấu X đỏ, ngược lại lấy dấu Tick xanh
    const iconClass = type === 'error' ? 'fa-solid fa-circle-xmark' : 'fa-solid fa-circle-check';

    // 3. Bỏ icon, nội dung chữ và thanh chạy progress vào trong
    toast.innerHTML = `
        <i class="${iconClass} toast-icon"></i>
        <span class="toast-text">${message}</span>
        <div class="toast-progress"></div>
    `;

    // 4. Đưa thông báo lên màn hình
    container.appendChild(toast);

    // 5. Hẹn giờ biến mất (Ví dụ 2000ms là 2 giây)
    setTimeout(function() {
        toast.classList.add('hiding');
        toast.addEventListener('animationend', function() {
            toast.remove();
        });
    }, 2000); 
}

// =========================================
// BANNER SLIDER LOGIC
// =========================================
let slideIndex = 0;
let autoSlideInterval;

function showSlides(index) {
    const track = document.getElementById("bannerTrack");
    const dots = document.querySelectorAll(".dot");
    const totalSlides = document.querySelectorAll(".slide").length;

    if (!track) return; // Nếu đang ở trang khác không có slider thì bỏ qua

    // Vòng lặp: Nếu quá ảnh 3 thì quay lại ảnh 1
    if (index >= totalSlides) slideIndex = 0;
    if (index < 0) slideIndex = totalSlides - 1;

    // Kéo khay ảnh trượt sang trái
    track.style.transform = `translateX(-${slideIndex * 100}%)`;

    // Chuyển màu cho dấu chấm tương ứng
    dots.forEach(dot => dot.classList.remove("active"));
    if(dots.length > 0) dots[slideIndex].classList.add("active");
}

// Hàm xử lý khi bấm nút Trái/Phải
function moveSlide(step) {
    showSlides(slideIndex += step);
    resetAutoSlide(); // Bấm tay thì reset lại bộ đếm tự động để không bị trượt kép
}

// Hàm xử lý khi bấm vào Dấu chấm
function currentSlide(index) {
    showSlides(slideIndex = index);
    resetAutoSlide();
}

// Tự động chuyển sau mỗi 4 giây
function startAutoSlide() {
    autoSlideInterval = setInterval(() => {
        moveSlide(1);
    }, 4000);
}

function resetAutoSlide() {
    clearInterval(autoSlideInterval);
    startAutoSlide();
}

function closeModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.remove('active');
    }
}
// Khởi động Slider ngay khi tải trang xong
document.addEventListener("DOMContentLoaded", () => {
    if(document.getElementById("bannerTrack")) {
        showSlides(slideIndex);
        startAutoSlide();
    }
});

document.addEventListener("DOMContentLoaded", function() {
    // Bắt sự kiện click cho tất cả các nút Thêm vào giỏ hàng
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-add-cart');
        
        if (btn) {
            e.preventDefault(); // Ngăn hành vi mặc định
            const productId = btn.getAttribute('data-id');

            // Đóng gói dữ liệu gửi đi
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);

            // Gửi AJAX ngầm đến file add_cart.php
            fetch('actions/add-cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    // Cập nhật con số đỏ trên icon giỏ hàng Header
                    const badge = document.getElementById('cart-badge-count');
                    if(badge) {
                        badge.innerText = data.total_items;
                        // Thêm hiệu ứng nảy nhẹ cho đẹp
                        badge.style.transform = 'scale(1.5)';
                        setTimeout(() => badge.style.transform = 'scale(1)', 200);
                    }
                    
                    // Bạn có thể đổi Alert này thành Toast (thông báo góc màn hình) cho xịn hơn
                    showToast('Đã thêm sản phẩm vào giỏ hàng!', 'success');
                }
            })
            .catch(error => console.error('Lỗi khi thêm giỏ hàng:', error));
        }
    });


    // ==========================================
    // XỬ LÝ TRANG GIỎ HÀNG (+, -, XÓA)
    // ==========================================
    const cartLayout = document.getElementById('cart-layout');

    if (cartLayout) {
        // Hàm tính toán lại Tổng tiền của toàn bộ giỏ hàng
        function recalculateCartTotal() {
            let newTotal = 0;
            // Tìm tất cả các cột thành tiền và cộng lại
            document.querySelectorAll('.cart-item-subtotal-v2').forEach(function(subtotalEl) {
                // Lọc bỏ dấu chấm và chữ ₫ để lấy số nguyên (VD: 1.500.000₫ -> 1500000)
                let priceVal = parseInt(subtotalEl.innerText.replace(/[^0-9]/g, ''));
                if (!isNaN(priceVal)) newTotal += priceVal;
            });

            // Cập nhật lên Bảng Tóm Tắt (Format lại thành chuẩn VNĐ)
            const formattedTotal = newTotal.toLocaleString('vi-VN') + '₫';
            document.getElementById('subtotal-val').innerText = formattedTotal;
            document.getElementById('total-val').innerText = formattedTotal;
        }

        cartLayout.addEventListener('click', function(e) {
            
            // 1. SỰ KIỆN BẤM NÚT THÙNG RÁC (XÓA)
            const delBtn = e.target.closest('.cart-del-btn');
            if (delBtn) {
                e.preventDefault();
                
                const productId = delBtn.getAttribute('data-id');
                const row = delBtn.closest('.cart-item-row-v2');

                // 1. Gọi Modal ra
                const modal = document.getElementById('deleteModal');
                const btnConfirm = document.getElementById('confirmDeleteBtn');
                
                if (modal && btnConfirm){
                    modal.classList.add('active');

                    const modalText = modal.querySelector('.modal-text');
                    if (modalText) modalText.innerText = 'Bạn có chắc chắn muốn bỏ sản phẩm này khỏi giỏ hàng không?';

                    btnConfirm.onclick = function(){
                        closeModal();

                        const formData = new FormData();
                        formData.append('product_id', productId);
                        formData.append('action', 'remove');
                        
                        fetch('actions/update-cart.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if(data.status === 'success') {
                                // Cập nhật số lượng trên icon giỏ hàng ở Header
                                // const badge = document.querySelector('.cart-btn .badge');
                                const badge = document.getElementById('cart-badge-count');
                                if (badge) badge.innerText = data.total_items;

                                showToast('Đã xóa sản phẩm khỏi giỏ hàng!', 'success');
                                // Xóa dòng HTML đó đi bằng hiệu ứng mờ dần
                                row.style.opacity = '0';
                                setTimeout(() => {
                                    row.remove();
                                    recalculateCartTotal(); // Tính lại tổng tiền
                                    
                                    // Nếu xóa hết sạch thì load lại trang để hiện "Giỏ hàng trống"
                                    if (data.total_items === 0) location.reload();
                                }, 300);
                            }
                        });
                    }
                }  
            }

            // 2. SỰ KIỆN BẤM CỘNG / TRỪ SỐ LƯỢNG
            const qtyBtn = e.target.closest('.cart-qty-btn');
            if (qtyBtn) {
                const productId = qtyBtn.getAttribute('data-id');
                const row = qtyBtn.closest('.cart-item-row-v2');
                const qtySpan = row.querySelector('.cart-qty-num');
                
                let currentQty = parseInt(qtySpan.innerText);

                // Tăng hoặc giảm
                if (qtyBtn.classList.contains('btn-minus')) {
                    if (currentQty <= 1) return; // Số lượng 1 thì ko cho trừ nữa
                    currentQty--;
                } else {
                    currentQty++;
                }

                // Cập nhật số lượng mới lên màn hình
                qtySpan.innerText = currentQty;

                // Tính toán Thành Tiền cho món đó
                const unitPriceEl = row.querySelector('.cart-item-unit-price');
                const unitPrice = parseInt(unitPriceEl.innerText.replace(/[^0-9]/g, ''));
                const newSubtotal = unitPrice * currentQty;
                
                row.querySelector('.cart-item-subtotal-v2').innerText = newSubtotal.toLocaleString('vi-VN') + '₫';

                // Cập nhật Tổng tiền đơn hàng
                recalculateCartTotal();

                // Gửi AJAX ngầm lưu vào Cookie
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('action', 'update');
                formData.append('quantity', currentQty);

                fetch('actions/update-cart.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        // const badge = document.querySelector('.cart-btn .badge');
                        const badge = document.getElementById('cart-badge-count');
                        if (badge) badge.innerText = data.total_items;
                    }
                });
            }
        });
    }
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
});

