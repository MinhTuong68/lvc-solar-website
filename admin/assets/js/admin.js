function toggleSidebar(){
    const sidebar = document.getElementById("sidebar")
    sidebar.classList.toggle("collapsed");
}

document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.toggle-group').forEach(groupTitle => {
         groupTitle.addEventListener('click', () => {
            const parent = groupTitle.parentElement;
            parent.classList.toggle('active');
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Chỉ đích danh ô input và ô báo lỗi
    const products_name = document.getElementById('products_name');
    const error_product_name = document.getElementById('error_product_name');


    // 2. Lắng nghe sự kiện 'input' (Mỗi lần gõ phím là mỗi lần chạy hàm này)
    products_name.addEventListener('input', function() {
        // Lấy giá trị đang gõ và xóa khoảng trắng 2 đầu
        const value = products_name.value.trim(); 

        // 3. Viết luật (Rules) kiểm tra
        if (value === '') {
            error_product_name.textContent = 'Tên sản phẩm không được để trống!';
            products_name.classList.add('input-error'); // Tô đỏ khung
        } 
        else if (value.length < 5) {
            error_product_name.textContent = 'Tên sản phẩm quá ngắn, phải từ 5 ký tự!';
            products_name.classList.add('input-error'); 
        } 
        else {
            // Nếu đúng luật -> Xóa dòng lỗi và xóa khung đỏ
            error_product_name.textContent = '';
            products_name.classList.remove('input-error');
        }
    });

    const slugInput = document.getElementById('products_slug');
    const slugError = document.getElementById('slug_error');
    slugInput.addEventListener('input', function(){
        const value = slugInput.value.trim();

        if(value == ''){
            slugError.textContent = 'Đường dẫn không được để trống!';
            slugInput.classList.add('input-error');
        }
        else if(value.includes(' ')){
            slugError.textContent = 'Đường dẫn không chứa khoản trắng!';
            slugInput.classList.add('input-error');
        }
        else{
            slugError.textContent = '';
            slugInput.classList.remove('input-error');
        }
        
    });

    const priceInput = document.getElementById('products_price');
    const priceError = document.getElementById('price_error');

    priceInput.addEventListener('input', function(){
        const value = priceInput.value.trim();

        if(value == ''){
            priceError.textContent = 'Giá không được để trống!';
            priceInput.classList.add('input-error');
        }
        else if(value < 0){
            priceError.textContent = 'Giá không được nhỏ hơn 0';
            priceInput.classList.add('input-error');
        }
        else{
            priceError.textContent = '';
            priceInput.classList.remove('input-error');
        }
    });

    const stockInput = document.getElementById('products_stock');
    const stockError = document.getElementById('stock_error');

    stockInput.addEventListener('input', function(){
        const value = stockInput.value.trim();

        if(value < 0){
            stockError.textContent = 'Số lượng không được nhỏ hơn 0';
            stockInput.classList.add('input-error');
        }
        else{
            stockError.textContent = '';
            stockInput.classList.remove('input-error');
        }
    });

    const addProductForm = document.getElementById('addProductForm');
    const loadingOverlay = document.getElementById('loading-overlay');
    const submitBtn = document.getElementById('btn-submit');
    let isVideoLoading = false;

    if (addProductForm && loadingOverlay){
        addProductForm.addEventListener('submit', function(event) {
            // event.preventDefault();
            // 1. Hiện màn hình loading sương mù lên
            if (isVideoLoading === true) {
                event.preventDefault(); // Chặn hành động gửi Form
                showToast("Vui lòng đợi video load xong rồi mới bấm Lưu!", "error");
                return; // Dừng chạy code bên dưới
            }

            loadingOverlay.style.display = 'flex';

            // 2. Làm liệt nút Submit, đổi màu xám
            if (submitBtn) {
                // submitBtn.disabled = true;
                submitBtn.style.background = '#94a3b8'; 
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
            }

            // setTimeout(function() {
                 
            //      // GỌI TOAST RA THAY CHO ALERT CŨ!
            //      showToast("Lưu sản phẩm thành công!");
                 
            //      // Nhả form ra lại như cũ
            //      loadingOverlay.style.display = 'none';
            //      submitBtn.disabled = false;
            //      submitBtn.style.background = '#10b981';
            //      submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> Lưu Sản Phẩm Mới';
            //      addProductForm.reset(); 
            // }, 2000);
        });
    }


    const galleryInput = document.getElementById('gallery-upload');
    const previewContainer = document.getElementById('gallery-preview-container');
    let selectedFiles = []; // Cái túi ảo (Mảng) để đựng các file đang được chọn

    if (galleryInput && previewContainer) {
        galleryInput.addEventListener('change', function(e) {
            // Khi người dùng chọn file, nhét thêm các file mới vào cái túi ảo
            const newFiles = Array.from(e.target.files);
            selectedFiles = selectedFiles.concat(newFiles);

            // Cập nhật lại màn hình (Vẽ ảnh ra) và cập nhật lại thẻ input
            updateGalleryPreview();
            updateFileInput();
        });
    }

    function updateGalleryPreview() {
        // Xóa sạch khung cũ để vẽ lại từ đầu
        previewContainer.innerHTML = ''; 
        
        selectedFiles.forEach((file, index) => {
            // 1. Tạo cái khung vuông
            const div = document.createElement('div');
            div.className = 'preview-item';

            // 2. Tạo hình ảnh bên trong
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file); // Hàm tự động biến file thành đường link để hiện ảnh

            // 3. Tạo nút X đỏ
            const btnX = document.createElement('button');
            btnX.className = 'btn-remove-preview';
            btnX.type = 'button'; // Rất quan trọng: Ngăn không cho nút này lỡ tay Submit Form
            btnX.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            
            // 4. Bắt sự kiện: Nếu bấm nút X này thì xóa file tương ứng trong túi ảo
            btnX.addEventListener('click', function() {
                selectedFiles.splice(index, 1); // Rút file ở vị trí index ra khỏi túi
                updateGalleryPreview(); // Gọi hàm tự vẽ lại màn hình
                updateFileInput(); // Cập nhật lại vào thẻ Input cho PHP
            });

            // Gắn ảnh và nút X vào khung vuông, rồi nhét khung vuông lên Web
            div.appendChild(img);
            div.appendChild(btnX);
            previewContainer.appendChild(div);
        });
    }

    // Hàm Lấy các file còn lại trong túi ảo nhét ngược vào lại thẻ Input
    function updateFileInput() {
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        galleryInput.files = dataTransfer.files;
    }

    // Load 1 images add-products.php
    const mainImageInput = document.getElementById('main-image-upload');
    const mainImagePreviewContainer = document.getElementById('main-image-preview-container');

    if (mainImageInput && mainImagePreviewContainer) {
        mainImageInput.addEventListener('change', function(e) {
            // Lấy ra file đầu tiên (và duy nhất) mà người dùng vừa chọn
            const file = e.target.files[0]; 
            
            // Xóa sạch khung preview cũ đi (để lỡ người ta chọn ảnh 2 thì mất ảnh 1)
            mainImagePreviewContainer.innerHTML = '';

            if (file) {
                // 1. Tạo khung hình vuông (Dùng lại class cũ của thư viện ảnh)
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.style.width = '120px'; // Cho ảnh chính to hơn thư viện ảnh 1 xíu
                div.style.height = '120px';

                // 2. Tạo hình ảnh
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);

                // 3. Tạo nút X đỏ
                const btnX = document.createElement('button');
                btnX.className = 'btn-remove-preview';
                btnX.type = 'button';
                btnX.innerHTML = '<i class="fa-solid fa-xmark"></i>';

                // 4. Sự kiện khi bấm nút X: Tẩy trắng thẻ input và xóa hình
                btnX.addEventListener('click', function() {
                    mainImageInput.value = ''; // Rút file ra khỏi input
                    mainImagePreviewContainer.innerHTML = ''; // Xóa hình trên màn hình
                });

                // Nhét ảnh và nút X vào khung
                div.appendChild(img);
                div.appendChild(btnX);
                mainImagePreviewContainer.appendChild(div);
            }
        });
    }

    // Load video add-products.php

    const videoInput = document.getElementById('video-upload');
    const videoPreviewContainer = document.getElementById('video-preview-container');

    if (videoInput && videoPreviewContainer) {
        videoInput.addEventListener('change', function(e) {
            const file = e.target.files[0]; 
            videoPreviewContainer.innerHTML = ''; // Xóa video cũ nếu có

            if (file) {
                isVideoLoading = true;
                if (submitBtn) {
                    // submitBtn.disabled = true;
                    submitBtn.style.background = '#94a3b8'; // Đổi màu xám
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang load video...';
                }
                // TÍNH NĂNG BẢO VỆ SERVER: Chặn video nặng hơn 20MB
                const maxSize = 100 * 1024 * 1024; // 20MB tính ra Byte
                if (file.size > maxSize) {
                    // Dùng Toast để báo lỗi cho đẹp thay vì dùng alert
                    showToast("Lỗi: Video quá nặng! Vui lòng chọn video dưới 100MB.", "error");
                    videoInput.value = ''; // Tẩy trắng dữ liệu
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.style.background = '#10b981'; // Trả về màu xanh
                        submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> Lưu Sản Phẩm Mới';
                    }
                    
                    // 2. FILE LỖI THÌ TẮT CỜ HIỆU ĐI ĐỂ KHÁCH CÒN BẤM LƯU LẠI
                    isVideoLoading = false;
                    return; // Ngừng chạy code bên dưới
                }

                // 1. Tạo khung (video thì làm form chữ nhật nằm ngang cho đẹp)
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.style.width = '240px'; 
                div.style.height = '135px';

                // 2. Tạo thẻ <video> thay vì <img>
                const video = document.createElement('video');
                video.src = URL.createObjectURL(file);
                video.controls = true; // Hiện nút Play/Pause
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'cover';

                // video.onloadeddata = function () {
                //     if (submitBtn) {
                //         submitBtn.disabled = false;
                //         submitBtn.style.background = '#10b981';
                //         submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> Lưu Sản Phẩm Mới';
                //     }
                    
                //     // 3. VIDEO LOAD THÀNH CÔNG -> TẮT CỜ HIỆU, CHO PHÉP SUBMIT
                //     isVideoLoading = false;
                //     showToast("Tải video xem trước thành công!", "success");
                // };
                video.onloadeddata = function () {
                    // ÉP TRÌNH DUYỆT ĐỢI 3 GIÂY (3000ms) ĐỂ NGẮM NÚT "ĐANG LOAD..."
                    setTimeout(function() {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.style.background = '#10b981';
                            submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> Lưu Sản Phẩm Mới';
                        }
                        isVideoLoading = false;
                        showToast("Tải video xem trước thành công!", "success");
                    }, 2000);
                };

                // 3. Tạo nút X đỏ
                const btnX = document.createElement('button');
                btnX.className = 'btn-remove-preview';
                btnX.type = 'button';
                btnX.innerHTML = '<i class="fa-solid fa-xmark"></i>';

                // 4. Bấm nút X thì xóa video
                btnX.addEventListener('click', function() {
                    videoInput.value = ''; 
                    videoPreviewContainer.innerHTML = ''; 
                    isVideoLoading = false;
                });

                div.appendChild(video);
                div.appendChild(btnX);
                videoPreviewContainer.appendChild(div);
            }else {
                // Nếu khách bấm "Hủy" ở cửa sổ chọn file, cũng phải tắt cờ
                isVideoLoading = false;
            }
        });
    }
});

// HÀM TẠO TOAST THÔNG BÁO DÙNG CHUNG CHO TOÀN ADMIN
//Thêm tham số type (Mặc định là 'success')
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