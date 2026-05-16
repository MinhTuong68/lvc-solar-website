// Hàm 1: Chỉ làm nhiệm vụ gọt chữ thành Slug
function generateSlug(text) {
    let slug = text.toLowerCase();
    
    slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
    slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
    slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
    slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
    slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
    slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
    slug = slug.replace(/đ/gi, 'd');
    
    slug = slug.replace(/\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi, '');
    slug = slug.replace(/ /gi, "-");
    slug = slug.replace(/\-\-\-\-\-/gi, '-');
    slug = slug.replace(/\-\-\-\-/gi, '-');
    slug = slug.replace(/\-\-\-/gi, '-');
    slug = slug.replace(/\-\-/gi, '-');
    
    slug = '@' + slug + '@';
    slug = slug.replace(/\@\-|\-\@|\@/gi, '');
    
    return slug;
}

// Hàm 2: Gọi tự động (Chỉ cần truyền ID ô nhập và ID ô xuất)
function autoSlug(inputId, outputId) {
    let inputEl = document.getElementById(inputId);
    let outputEl = document.getElementById(outputId);
    
    if(inputEl && outputEl) {
        inputEl.addEventListener('keyup', function() {
            // 1. Tự động điền slug
            outputEl.value = generateSlug(this.value);
            
            // 2. Tự động kích hoạt sự kiện để chạy đoạn AJAX check trùng bên dưới
            outputEl.dispatchEvent(new Event('input'));
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const slugInput = document.getElementById('products_slug');
    const btnSubmit = document.getElementById('btn-submit');
    
    // Tạo 1 dòng chữ nhỏ dưới ô input để báo lỗi
    let errorSpan = document.createElement('small');
    errorSpan.style.display = 'block';
    errorSpan.style.marginTop = '5px';
    slugInput.parentNode.appendChild(errorSpan);

    slugInput.addEventListener('input', function() {
        const slugValue = this.value.trim();
        
        if(slugValue === '') {
            errorSpan.textContent = '';
            slugInput.style.borderColor = '';
            btnSubmit.disabled = false;
            return;
        }

        // Gửi AJAX ngầm lên file PHP vừa tạo
        const formData = new FormData();
        formData.append('slug', slugValue);

        fetch('actions/check-slug.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.exists) {
                // Bị trùng -> Báo đỏ, khóa nút Lưu
                errorSpan.textContent = '❌ Tên đường dẫn (Slug) này đã tồn tại!';
                errorSpan.style.color = 'red';
                slugInput.style.borderColor = 'red';
                btnSubmit.disabled = true; 
            }
        });
    });
});