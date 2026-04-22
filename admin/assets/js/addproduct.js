function setupTogglePanel(btnId, wrapperId) {
    const toggleBtn = document.getElementById(btnId);
    const wrapperPanel = document.getElementById(wrapperId);

    if (toggleBtn && wrapperPanel) {
        toggleBtn.addEventListener('click', function(event) {
            event.preventDefault(); // Ngăn chặn lỗi load lại trang
            
            // Bật/tắt hiển thị hộp
            wrapperPanel.classList.toggle('show');
            
            // Đổi giao diện nút bấm
            if (wrapperPanel.classList.contains('show')) {
                toggleBtn.innerHTML = '<i class="fa-solid fa-xmark"></i> Đóng lại';
                toggleBtn.style.backgroundColor = '#ef4444'; // Nút đỏ
                toggleBtn.style.borderColor = '#ef4444';
            } else {
                toggleBtn.innerHTML = '<i class="fa-solid fa-plus"></i> Thêm sản phẩm';
                toggleBtn.style.backgroundColor = ''; // Trả về màu gốc
                toggleBtn.style.borderColor = '';
            }
        });
    }
}


// Hàm Lọc Sản phẩm theo Danh mục
function filterCategory(categoryId) {
    // 1. Đổi màu nút danh mục đang chọn
    const buttons = document.querySelectorAll('.cat-btn');
    buttons.forEach(btn => {
        btn.style.background = 'white';
        btn.style.color = '#475569';
        btn.style.border = '1px solid #cbd5e1';
    });
    const clickedBtn = event.currentTarget;
    clickedBtn.style.background = '#3b82f6';
    clickedBtn.style.color = 'white';
    clickedBtn.style.border = 'none';

    // 2. Ẩn/Hiện thẻ sản phẩm
    const products = document.querySelectorAll('.product-item');
    products.forEach(item => {
        if (categoryId === 'all' || item.getAttribute('data-category') == categoryId) {
            item.style.display = 'flex'; // Hiện
        } else {
            item.style.display = 'none'; // Ẩn
        }
    });
}
