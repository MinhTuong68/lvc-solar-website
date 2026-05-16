/**
 * Hàm dùng chung để thêm sản phẩm vào giỏ hàng (AJAX)
 * @param {number} productId - ID của sản phẩm
 * @param {number|string} qty - Số lượng cần thêm
 * @param {string} actionType - 'add_cart' (chỉ thêm) hoặc 'buy_now' (thêm xong qua giỏ hàng)
 */
function addToCartAjax(productId, qty, actionType) {
    // Đảm bảo số lượng luôn là số nguyên (quan trọng cho trang chi tiết)
    let quantity = parseInt(qty);
    if (isNaN(quantity) || quantity <= 0) {
        quantity = 1;
    }

    // 1. Gói dữ liệu gửi đi
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
    
    // 2. Gọi file PHP xử lý ngầm
    fetch('actions/add-cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log("DỮ LIỆU TỪ PHP TRẢ VỀ LÀ:", data);
        if (data.status === 'success') {
            
            // --- YÊU CẦU 1: TỰ ĐỘNG TĂNG SỐ VÀ CÓ HIỆU ỨNG TRÊN ICON GIỎ HÀNG ---
            // Tìm tất cả các thẻ chứa số lượng giỏ hàng trên Header
            // Bắt TẤT CẢ các thẻ có khả năng là cục số giỏ hàng
            const cartBadges = document.querySelectorAll('.badge');
            cartBadges.forEach(badge => {
                badge.innerText = data.new_items;

                badge.style.display = '';
                
                // Thêm hiệu ứng nảy (scale) giống trang products
                badge.style.transition = 'transform 0.2s ease-in-out';
                badge.style.transform = 'scale(1.4)';
                setTimeout(() => {
                    badge.style.transform = 'scale(1)';
                }, 200);
            });

            // --- YÊU CẦU 2: PHÂN LUỒNG MUA NGAY / THÊM GIỎ ---
            if (actionType === 'buy_now') {
                // Nếu bấm MUA NGAY -> Chuyển thẳng qua trang Giỏ hàng
                window.location.href = 'index.php?page=cart';
            } else {
                // Nếu bấm THÊM VÀO GIỎ -> Ở lại trang và hiện thông báo
                if (typeof showToast === 'function') {
                    showToast('Đã thêm ' + quantity + ' sản phẩm vào giỏ hàng!', 'success');
                }
                if (typeof updateCartDropdown === 'function') {
                    updateCartDropdown(data.cart_items, data.root_url);
                }
                 else if (typeof toast === 'function') {
                    toast({title: "Thành công", message: "Đã thêm vào giỏ hàng", type: "success"});
                } else {
                    alert('Đã thêm sản phẩm vào giỏ hàng!'); // Backup
                }
            }
            
        } else {
            // Lỗi từ server trả về
            if (typeof showToast === 'function') {
                showToast('Lỗi: ' + data.message, 'error');
            } else {
                alert('Lỗi: Không thể thêm vào giỏ');
            }
        }
    })
    .catch(error => {
        console.error('Lỗi khi thêm giỏ hàng:', error);
        if (typeof showToast === 'function') {
            showToast('Lỗi kết nối máy chủ!', 'error');
        }
    });
}