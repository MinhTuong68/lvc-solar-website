let deleteUrl = ""; 

function openModal(id, type) {
    document.getElementById('deleteModal').classList.add('active');
    
    // Gọi thẳng vào thư mục actions/delete.php kèm theo type (brand, product...)
    deleteUrl = "actions/delete.php?type=" + type + "&id=" + id;
}

function closeModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

// BẮT BUỘC PHẢI BỌC ĐOẠN NÀY LẠI ĐỂ TRÁNH LỖI KHI ĐỂ SCRIPT TRÊN HEADER
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            window.location.href = deleteUrl;
        });
    }
});