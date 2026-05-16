<?php
    include_once("../classes/news.php");
    $news_obj = new News($conn);

    // 1. Lấy ID từ URL
    if (!isset($_GET['id'])) {
        header("Location: index.php?page=manage-news");
        exit;
    }

    $id = (int)$_GET['id'];
    $news_item = $news_obj->getNewsByID($id);

    // Nếu không tìm thấy bài viết thì đá về trang quản lý
    if (!$news_item) {
        $_SESSION['toast_message'] = "Không tìm thấy bài viết!";
        $_SESSION['toast_type'] = "error";
        header("Location: index.php?page=manage-news");
        exit;
    }
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="page-header-v2">
            <h2 class="page-title">Cập nhập biết bài</h2>
        </div>

        <form action="actions/edit-news.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $news_item['id'] ?>">
            <input type="hidden" name="old_image" value="<?= $news_item['image'] ?>"></div>
            <div class="card-v2 p-4">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label>Tiêu đề bài viết <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($news_item['title']) ?>" required placeholder="Ví dụ: Lợi ích của điện mặt trời">
                        </div>

                        <div class="form-group mb-3">
                            <label>Đường dẫn (Slug) - Tự động tạo</label>
                            <input type="text" name="slug" id="slug" value="<?= htmlspecialchars($news_item['slug']) ?>" class="form-control">
                        </div>

                        <div class="form-group mb-3">
                            <label>Mô tả ngắn (Tóm tắt hiện ngoài danh sách)</label>
                            <textarea name="summary" class="form-control" rows="3"><?= htmlspecialchars($news_item['summary']) ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Ảnh đại diện bài viết</label>
                            <?php if(!empty($news_item['image'])): ?>
                                <img src="<?php echo ROOT_URL ?>/uploads/news/images/<?= $news_item['image'] ?>" alt="Ảnh cũ" style="width: 100%; max-height: 160px; object-fit: cover; border-radius: 8px; margin-bottom: 15px; border: 1px solid var(--gray-200);">
                            <?php endif; ?>
                            
                            <input type="file" name="image" class="form-control">
                            <small class="text-muted" style="display: block; margin-top: 5px;">* Để trống nếu không muốn đổi ảnh mới</small>
                        </div>
                        <div class="form-group mb-3">
                            <label>Trạng thái</label>
                            <select name="status" class="form-control">
                                <option value="1" <?= ($news_item['status'] == 1) ? 'selected' : '' ?>>Hiển thị ngay</option>
                                <option value="0" <?= ($news_item['status'] == 0) ? 'selected' : '' ?>>Lưu bản nháp</option>
                            </select>
                        </div>
                    </div>
                </div><br>

                <div class="form-group">
                    <label><strong>Nội dung chi tiết bài viết</strong></label>
                    <textarea name="content" id="content" class="form-control news-content-editor" rows="12"><?= htmlspecialchars($news_item['content']) ?></textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" name="btn_edit_news" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Lưu thay đổi
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