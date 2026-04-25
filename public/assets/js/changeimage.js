
function changePjMedia(thumbElement, imgUrl) {
    // 1. Lấy thẻ ảnh to và thay đổi đường dẫn (src)
    document.getElementById('pjMainImage').src = imgUrl;

    // 2. Lấy tất cả các thẻ ảnh nhỏ
    let thumbs = document.querySelectorAll('.project-thumb-item');
    
    // 3. Xóa class 'active' khỏi tất cả ảnh nhỏ
    thumbs.forEach(function(el) {
        el.classList.remove('active');
    });
    
    // 4. Thêm class 'active' vào đúng cái ảnh nhỏ vừa click
    thumbElement.classList.add('active');
}
