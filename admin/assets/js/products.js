// 1. XÓA ẢNH TRỰC TIẾP KHỎI DATABASE (DÙNG CHUNG MODAL NHƯNG KHÔNG ĐỤNG DELETE.JS)
function removeOldGallery(galId) {
    // A. Mở giao diện Modal lên
    const modal = document.getElementById('deleteModal');
    modal.classList.add('active');

    // B. TUYỆT CHIÊU "TẨY NÃO" NÚT XÁC NHẬN
    // Lấy cái nút Xác nhận cũ (đang bị delete.js ám sự kiện chuyển trang)
    const oldBtn = document.getElementById('confirmDeleteBtn');
    
    // Tạo ra một bản sao y hệt, nhưng bản sao này sẽ KHÔNG copy các sự kiện click cũ
    const newBtn = oldBtn.cloneNode(true); 
    
    // Tráo đổi nút mới vào chỗ nút cũ
    oldBtn.parentNode.replaceChild(newBtn, oldBtn);

    // C. GẮN SỰ KIỆN AJAX VÀO NÚT MỚI (Mọi thứ giờ chỉ nằm gọn trong file product.js)
    newBtn.addEventListener('click', function() {
        // Đổi chữ để người dùng biết đang xóa
        const originalText = newBtn.innerHTML;
        newBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xóa...';
        newBtn.disabled = true;

        const formData = new FormData();
        formData.append('gal_id', galId);

        fetch('actions/ajax-delete-gallery.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                // Xóa ảnh trên màn hình
                document.getElementById('gal_' + galId).remove();
                updateGalleryCounter();
                
                // Đóng modal
                modal.classList.remove('active');
                
                // GỌI THÔNG BÁO BẰNG JS Ở ĐÂY
                showToastJS("Đã xóa ảnh khỏi hệ thống!", "success");
                
                // XONG VIỆC: Trả lại nút cũ
                newBtn.parentNode.replaceChild(oldBtn, newBtn);
            }
        })
        .catch(error => {
            showToastJS("Xóa ảnh không thành công. Vui lòng thử lại!", "error"); // Hiện thông báo lỗi màu đỏ
            newBtn.innerHTML = originalText;
            newBtn.disabled = false;
        });
    });

    // D. ĐỀ PHÒNG TRƯỜNG HỢP BẤM HỦY
    // Nếu họ không xóa mà bấm nút "Hủy", ta cũng phải trả lại nút gốc cho delete.js
    const cancelBtn = modal.querySelector('.form-btn-light'); // Tìm nút Hủy trong Modal
    if(cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            newBtn.parentNode.replaceChild(oldBtn, newBtn);
        }, {once: true}); // Lệnh once: true giúp sự kiện tự hủy sau khi bấm
    }
}

// 2. THÊM ẢNH VÀ LƯU TRỰC TIẾP VÀO DATABASE (AJAX)
document.addEventListener('DOMContentLoaded', () => {
    const newGalleryInput = document.getElementById('edit_product_new_gallery');
    const productIdInput = document.querySelector('input[name="id"]'); 
    
    if(newGalleryInput && productIdInput) {
        newGalleryInput.addEventListener('change', function(e) {
            if(this.files.length === 0) return;

            const container = document.getElementById('old-gallery-container');
            const formData = new FormData();
            
            // Gói ID sản phẩm và toàn bộ file ảnh lại
            formData.append('product_id', productIdInput.value);
            for(let i = 0; i < this.files.length; i++) {
                formData.append('new_gallery[]', this.files[i]);
            }

            // Đổi nút thành Đang tải để user biết
            const label = document.querySelector('label[for="edit_product_new_gallery"]');
            const oldLabelText = label.innerText;
            label.innerText = "Đang tải ảnh lên hệ thống...";

            // Bắn qua API Thêm
            fetch('actions/ajax-add-gallery.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    // Vẽ các ảnh vừa lưu thành công ra màn hình chung với list cũ
                    let stt = document.querySelectorAll('.form-gallery-item').length + 1;
                    
                    data.images.forEach(img => {
                        const item = document.createElement('div');
                        item.className = 'form-gallery-item';
                        item.id = 'gal_' + img.id; // Gắn ID thật từ DB
                        item.setAttribute('draggable', 'true');
                        
                        item.innerHTML = `
                            <div class="form-gallery-thumb">
                                <img src="../uploads/products/image_gallery/${img.image_path}" alt="Gallery image">
                            </div>
                            <button type="button" class="form-gallery-remove" onclick="removeOldGallery(${img.id})" title="Xóa ảnh này">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            <div class="form-gallery-meta">
                                <span>Ảnh #${stt++} Ảnh mới thêm</i></span>
                            </div>
                            <input type="hidden" name="gallery_sort_order[]" value="${img.id}">
                        `;
                        container.appendChild(item);
                    });
                    
                    // Reset input và giao diện
                    newGalleryInput.value = '';
                    label.innerText = oldLabelText;
                    updateGalleryCounter();

                    showToastJS("Đã thêm ảnh mới thành công!", "success");
                }
            })
            .catch(error => {
                showToastJS("Xóa ảnh không thành công. Vui lòng thử lại!", "error"); // Hiện thông báo lỗi màu đỏ
                label.innerText = oldLabelText;
            });
        });
    }
});

function updateGalleryCounter() {
    const counterElement = document.querySelector('.form-gallery-counter strong');
    if (counterElement) {
        counterElement.textContent = document.querySelectorAll('.form-gallery-item').length;
    }
}

// 3. THÊM THÔNG SỐ (Giữ nguyên)
function addSpecRow() {
    const container = document.getElementById('lvcEpSpecsContainer');
    if(!container) return;
    
    const row = document.createElement('div');
    row.className = 'form-spec-row';
    row.innerHTML = `
        <input type="text" name="spec_names[]" class="form-input" placeholder="Tên thông số">
        <input type="text" name="spec_values[]" class="form-input" placeholder="Giá trị">
        <button type="button" class="form-spec-remove" onclick="this.parentElement.remove()" title="Xóa dòng này">
            <i class="fa-solid fa-trash-can"></i>
        </button>
    `;
    container.appendChild(row);
}

// HÀM TẠO THÔNG BÁO (TOAST) BẰNG JAVASCRIPT
function showToastJS(message, type) {
    const toast = document.createElement('div');
    
    // Style giao diện giống hình: Nền trắng, chữ đen, viền bóng
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.background = '#ffffff';
    toast.style.color = '#333333';
    toast.style.padding = '15px 20px';
    toast.style.minWidth = '250px';
    toast.style.borderRadius = '6px';
    toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    toast.style.zIndex = '9999';
    toast.style.transition = 'all 0.3s ease';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '12px';
    toast.style.overflow = 'hidden'; // Quan trọng

    const iconHtml = type === 'success' 
        ? '<i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 24px;"></i>' 
        : '<i class="fa-solid fa-circle-xmark" style="color: #ef4444; font-size: 24px;"></i>';

    toast.innerHTML = `
        ${iconHtml}
        <span style="font-size: 15px; font-weight: 500;">${message}</span>
    `;

    // 2. Tạo thanh thời gian chạy dưới đáy
    const progressBar = document.createElement('div');
    progressBar.style.position = 'absolute';
    progressBar.style.bottom = '0';
    progressBar.style.left = '0'; // Neo ở bên trái để khi thu nhỏ nó chạy từ phải qua trái
    progressBar.style.height = '4px';
    progressBar.style.width = '100%'; // Bắt đầu với độ dài 100%
    progressBar.style.background = type === 'success' ? '#10b981' : '#ef4444';
    progressBar.style.transition = 'width 2s linear'; // Ép thời gian thu nhỏ là đúng 3 giây, chạy đều (linear)

    // Nhét thanh chạy vào trong thông báo, rồi đẩy thông báo ra màn hình
    toast.appendChild(progressBar);
    document.body.appendChild(toast);

    // 3. Kích hoạt hiệu ứng rút ngắn thanh chạy
    // Phải dùng setTimeout 10ms để trình duyệt kịp vẽ ra cái thanh 100% trước, rồi mới ép nó về 0%
    setTimeout(() => {
        progressBar.style.width = '0%';
    }, 10);

    // 4. Tự động mờ và biến mất sau 3 giây (Khớp với thời gian thanh chạy)
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)'; // Trượt nhẹ lên trên lúc biến mất
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// ==========================================
// TÍNH NĂNG KÉO THẢ (DRAG & DROP) SẮP XẾP ẢNH
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('old-gallery-container');
    let draggedItem = null;

    if (!container) return;

    // Khi bắt đầu nhấp chuột kéo 1 tấm ảnh
    container.addEventListener('dragstart', function(e) {
        const item = e.target.closest('.form-gallery-item');
        if (item) {
            draggedItem = item;
            // Làm mờ ảnh đang cầm đi một chút cho đẹp
            setTimeout(() => item.style.opacity = '0.4', 0);
        }
    });

    // Khi di chuyển ảnh đi ngang qua các ảnh khác
    container.addEventListener('dragover', function(e) {
        e.preventDefault(); // Rất quan trọng, bắt buộc có để cho phép Drop
        const item = e.target.closest('.form-gallery-item');
        
        // Hoán đổi vị trí của 2 phần tử HTML ngay trên màn hình
        if (item && item !== draggedItem) {
            const children = Array.from(container.querySelectorAll('.form-gallery-item'));
            const draggedIndex = children.indexOf(draggedItem);
            const targetIndex = children.indexOf(item);

            if (draggedIndex < targetIndex) {
                item.after(draggedItem); // Kéo xuống dưới
            } else {
                item.before(draggedItem); // Kéo lên trên
            }
        }
    });

    // Khi buông chuột thả ảnh xuống
    container.addEventListener('dragend', function(e) {
        if (draggedItem) {
            draggedItem.style.opacity = '1'; // Phục hồi độ nét
            draggedItem = null;
            
            // 1. Chạy hàm cập nhật lại chữ "Ảnh #1, Ảnh #2..." trên màn hình
            updateGalleryMetaText();
            
            // 2. Chạy hàm bắn AJAX lưu thứ tự mới xuống Database
            saveGalleryOrderAjax();
        }
    });
});

// Hàm cập nhật lại số thứ tự hiển thị trên ảnh
function updateGalleryMetaText() {
    const items = document.querySelectorAll('.form-gallery-item');
    items.forEach((item, index) => {
        const metaSpan = item.querySelector('.form-gallery-meta span');
        if (metaSpan) {
            metaSpan.innerHTML = `Ảnh #${index + 1}`;
        }
    });
}

// Hàm gửi mảng ID mới xuống Server (Bấm phát ăn ngay)
function saveGalleryOrderAjax() {
    // Lấy tất cả các thẻ input ẩn chứa ID ảnh đang có trên màn hình
    const inputs = document.querySelectorAll('input[name="gallery_sort_order[]"]');
    const formData = new FormData();
    
    // Gom tất cả ID thành 1 mảng để gửi đi
    inputs.forEach(input => {
        formData.append('sort_order[]', input.value);
    });

    // Gọi API
    fetch('actions/ajax-sort-gallery.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            showToastJS(data.message, 'success');
        }
    })
    .catch(error => {
        showToastJS("Lỗi khi lưu vị trí ảnh!", 'error');
    });
}