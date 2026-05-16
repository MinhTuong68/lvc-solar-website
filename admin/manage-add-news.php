<div class="wrapper">
    <div class="container-fluid">
        <div class="page-header-v2">
            <h2 class="page-title">Viết bài mới</h2>
        </div>

        <form action="actions/add-news.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="card-v2 p-4">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label>Tiêu đề bài viết <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" required placeholder="Ví dụ: Lợi ích của điện mặt trời">
                        </div>

                        <div class="form-group mb-3">
                            <label>Đường dẫn (Slug) - Tự động tạo</label>
                            <input type="text" name="slug" id="slug" class="form-control" readonly>
                        </div>

                        <div class="form-group mb-3">
                            <label>Mô tả ngắn (Tóm tắt hiện ngoài danh sách)</label>
                            <textarea name="summary" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Ảnh đại diện bài viết</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group mb-3">
                            <label>Trạng thái</label>
                            <select name="status" class="form-control">
                                <option value="1">Hiển thị ngay</option>
                                <option value="0">Lưu bản nháp</option>
                            </select>
                        </div>
                    </div>
                </div><br>

                <div class="form-group">
                    <label><strong>Nội dung chi tiết bài viết</strong></label>
                    <textarea name="content" 
                    id="content" 
                    class="form-control news-content-editor" 
                    rows="12"
                    placeholder="Viết nội dung chi tiết bài viết ở đây..."></textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" name="btn_add_news" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Đăng bài viết
                    </button>
                    <a href="index.php?page=manage-news" class="btn btn-outline">Hủy bỏ</a>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    autoSlug('title', 'slug');
</script>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('content',{
        filebrowserUploadUrl: '<?php echo ROOT_URL; ?>actions/upload-ckeditor.php',
        filebrowserUploadMethod: 'xhr',
        uploadUrl: 'actions/upload-ckeditor.php'
    }); 
</script>