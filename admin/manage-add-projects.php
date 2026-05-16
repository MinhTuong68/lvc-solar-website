<div class="wrapper">
    <div class="container-fluid">
        <div class="page-header-v2">
            <h2 class="page-title"><i class="fa-solid fa-solar-panel" style="color: var(--amber); margin-right: 10px;"></i> Thêm Dự Án Thi Công Mới</h2>
        </div><br>

        <form action="actions/add-projects.php" method="POST" enctype="multipart/form-data">
            <div class="card-v2 p-4">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Tên dự án <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="project_name" class="form-control" required placeholder="Ví dụ: Dự án 50kWp tại Nhà máy may Vinatex Bạc Liêu">
                        </div>

                        <div class="form-group">
                            <label>Đường dẫn (Slug) - Tự động tạo</label>
                            <input type="text" name="project_slug" id="project_slug" class="form-control" readonly>
                        </div>

                        <div class="project-form-row">
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; color: var(--gray-600); text-transform: uppercase;">Chủ đầu tư / Khách hàng</label>
                                <input type="text" name="client" class="form-control" placeholder="VD: Anh Tuấn / Công ty ABC">
                            </div>
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; color: var(--gray-600); text-transform: uppercase;">Ngày hoàn thành</label>
                                <input type="date" name="completion_date" class="form-control">
                            </div>
                        </div>

                        <div class="project-form-row">
                            <div class="project-form-col">
                                <label class="project-label-upper">Địa điểm thi công</label>
                                <input type="text" name="location" class="form-control" placeholder="VD: Huyện Hòa Bình, Bạc Liêu">
                            </div>
                            <div class="project-form-col">
                                <label class="project-label-upper">Công suất hệ thống</label>
                                <input type="text" name="capacity" class="form-control" placeholder="VD: 15 kWp">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Mô tả ngắn (Tóm tắt hiện ngoài danh sách)</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Ghi chú ngắn gọn về những điểm nổi bật của dự án này..."></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ảnh đại diện (Thumbnail chính) <span class="text-danger">*</span></label>
                            <div class="upload-zone">
                                <i class="fa-regular fa-image" style="font-size: 24px; color: #94a3b8; margin-bottom: 10px;"></i>
                                <input type="file" name="image" class="form-control" accept="image/*" required style="border: none; padding: 0; background: transparent;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Thư viện ảnh thi công (Nhiều ảnh)</label>
                            <div class="upload-zone">
                                <i class="fa-solid fa-images" style="font-size: 24px; color: #94a3b8; margin-bottom: 10px;"></i>
                                <input type="file" name="gallery[]" multiple class="form-control" accept="image/*" style="border: none; padding: 0; background: transparent;">
                                <small style="color: #64748b; display: block; margin-top: 5px;">Giữ Ctrl để chọn nhiều ảnh</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Dự án tiêu biểu (Ghim trang chủ)</label>
                            <select name="is_featured" class="form-control">
                                <option value="0">Bình thường</option>
                                <option value="1">Dự án Nổi bật (Ghim)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="status" class="form-control">
                                <option value="1">Hiển thị ngay</option>
                                <option value="0">Lưu bản nháp (Ẩn)</option>
                            </select>
                        </div>
                    </div>
                </div><br>

                <div class="form-group">
                    <label><strong>Nội dung chi tiết dự án (Quá trình thi công, vật tư, hiệu quả...)</strong></label>
                    <textarea name="content" 
                        id="content" 
                        class="form-control news-content-editor" 
                        rows="15"
                        placeholder="Viết nội dung chi tiết bài viết ở đây..."></textarea>
                </div>

                <div class="mt-4" style="border-top: 1px solid #e2e8f0; padding-top: 20px; text-align: right;">
                    <a href="index.php?page=manage-projects" class="btn btn-outline">Hủy bỏ</a>
                    <button type="submit" name="btn_add_project" class="btn btn-primary" style="padding: 12px 25px; font-size: 16px;">
                        <i class="fa-solid fa-save"></i> Lưu Dự Án
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    autoSlug('project_name', 'project_slug');
</script>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('content',{
        filebrowserUploadUrl: '<?php echo ROOT_URL; ?>actions/upload-ckeditor.php',
        filebrowserUploadMethod: 'xhr',
        uploadUrl: 'actions/upload-ckeditor.php',
        height: 600
    }); 
</script>