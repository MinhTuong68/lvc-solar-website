// ==============================================================
// FILE JS ĐIỀU KHIỂN MODAL DÙNG CHUNG
// Tương thích với ID gốc: #deleteModal, #confirmDeleteBtn
// ==============================================================

// Biến toàn cục để lưu trữ hành động (hàm) sẽ thực thi khi bấm nút Đồng ý
let currentModalCallback = null;

/**
 * Hàm gọi hiển thị Modal với nội dung tùy chỉnh
 * @param {string} title - Tiêu đề của Modal
 * @param {string} text - Nội dung chi tiết
 * @param {string} iconClass - Class của FontAwesome (VD: 'fa-solid fa-check')
 * @param {string} btnConfirmText - Chữ hiển thị trên nút Xác nhận
 * @param {function} callback - Hành động sẽ chạy khi bấm Xác nhận (Tùy chọn)
 * @param {boolean} hideCancel - Có ẩn nút Hủy đi không? (Mặc định là false)
 */
window.openModal = function(title, text, iconClass, btnConfirmText, callback = null, hideCancel = false) {
    const modal = document.getElementById('deleteModal');
    if (!modal) return;

    // 1. Bơm dữ liệu mới vào các thẻ tương ứng
    modal.querySelector('.modal-title').innerText = title;
    modal.querySelector('.modal-text').innerText = text;
    modal.querySelector('.modal-icon').innerHTML = `<i class="${iconClass}"></i>`;
    document.getElementById('confirmDeleteBtn').innerText = btnConfirmText;

    // 2. Xử lý nút Hủy (nếu chỉ muốn hiện thông báo 1 chiều thì ẩn nút Hủy đi)
    const cancelBtn = modal.querySelector('.btn-cancel');
    if (cancelBtn) {
        cancelBtn.style.display = hideCancel ? 'none' : 'inline-block';
    }

    // 3. Lưu lại hành động và Hiển thị Modal
    currentModalCallback = callback;
    modal.classList.add('active');
};

/**
 * Hàm đóng Modal (Đã được gọi trực tiếp trong onclick="closeModal()" của HTML)
 */
window.closeModal = function() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.remove('active');
    }
    currentModalCallback = null; // Reset lại hành động
};

// ==============================================================
// Lắng nghe sự kiện click vào nút Xác nhận (#confirmDeleteBtn)
// ==============================================================
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            // Nếu có truyền vào 1 hành động (callback) thì thực thi nó
            if (currentModalCallback && typeof currentModalCallback === 'function') {
                currentModalCallback();
            }
            
            // Chạy xong thì tự động đóng Modal
            closeModal();
        });
    }
});