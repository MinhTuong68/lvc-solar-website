
// Hàm BẬT sương mù (Có thể tùy chỉnh chữ)
window.showLoading = function(text = 'Đang xử lý, vui lòng đợi...') {
    document.getElementById('loadingText').innerText = text;
    document.getElementById('globalLoading').classList.add('active');
}

// Hàm TẮT sương mù
window.hideLoading = function() {
    document.getElementById('globalLoading').classList.remove('active');
}
