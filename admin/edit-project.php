<?php
    include_once("../classes/projects.php");
    $project_obj = new Project($conn);

    // 1. Lấy ID dự án từ URL
    if (!isset($_GET['id'])) {
        header("Location: index.php?page=manage-projects");
        exit;
    }

    $id = (int)$_GET['id'];
    $pj = $project_obj->getProjectByID($id);

    // Nếu không có dự án này thì quay về trang quản lý
    if (!$pj) {
        $_SESSION['toast_message'] = "Dự án không tồn tại!";
        $_SESSION['toast_type'] = "error";
        header("Location: index.php?page=manage-projects");
        exit;
    }
    $query_gal = mysqli_query($conn, "SELECT * FROM tbl_project_gallery WHERE project_id = $id ORDER BY sort_order ASC");
    $galleries = [];
    if ($query_gal && mysqli_num_rows($query_gal) > 0) {
        while ($row = mysqli_fetch_assoc($query_gal)) {
            $galleries[] = $row;
        }
    }
?>
<div class="wrapper">
    <div class="container-fluid">
        <div class="page-header-v2">
            <h2 class="page-title"><i class="fa-solid fa-solar-panel" style="color: var(--amber); margin-right: 10px;"></i> Thêm Dự Án Thi Công Mới</h2>
        </div><br>

        <form action="actions/edit-projects.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $pj['id'] ?>">
            <input type="hidden" name="old_image" value="<?= $pj['image'] ?>">
            
            <div class="card-v2 p-4">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Tên dự án <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="project_name" class="form-control" value="<?= htmlspecialchars($pj['name']) ?>" required placeholder="Ví dụ: Dự án 50kWp tại Nhà máy may Vinatex Bạc Liêu">
                        </div>

                        <div class="form-group">
                            <label>Đường dẫn (Slug) - Tự động tạo</label>
                            <input type="text" name="project_slug" id="project_slug" class="form-control" value="<?= htmlspecialchars($pj['slug']) ?>">
                        </div>

                        <div class="project-form-row">
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; color: var(--gray-600); text-transform: uppercase;">Chủ đầu tư / Khách hàng</label>
                                <input type="text" name="client" class="form-control" value="<?= htmlspecialchars($pj['client']) ?>" placeholder="VD: Anh Tuấn / Công ty ABC">
                            </div>
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; color: var(--gray-600); text-transform: uppercase;">Ngày hoàn thành</label>
                                <input type="date" name="completion_date" class="form-control" value="<?= $pj['completion_date'] ?>">
                            </div>
                        </div>

                        <div class="project-form-row">
                            <div class="project-form-col">
                                <label class="project-label-upper">Địa điểm thi công</label>
                                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($pj['location']) ?>" placeholder="VD: Huyện Hòa Bình, Bạc Liêu">
                            </div>
                            <div class="project-form-col">
                                <label class="project-label-upper">Công suất hệ thống</label>
                                <input type="text" name="capacity" class="form-control" value="<?= htmlspecialchars($pj['capacity']) ?>" placeholder="VD: 15 kWp">
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 15px;">
                            <label>Mô tả ngắn dự án</label>
                            <textarea name="description" class="form-control" rows="10"><?= htmlspecialchars($pj['description']) ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ảnh đại diện dự án</label><br>
                            <?php if(!empty($pj['image'])): ?>
                                <img src="<?php echo ROOT_URL ?>/uploads/projects/images/<?= $pj['image'] ?>" style="width: 100%; max-height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; border: 1px solid #ddd;">
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control">
                            <small class="text-muted">* Để trống nếu không muốn đổi ảnh</small>
                        </div>

                        <div class="form-group" style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--gray-200);">
                            <label style="font-size: 1.1rem;"><strong>Thư viện ảnh dự án (Kéo thả để sắp xếp)</strong></label>
                            
                            <div class="form-gallery" id="project-gallery-container" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 15px; margin-top: 10px;">
                                <?php if (!empty($galleries)): ?>
                                    <?php foreach ($galleries as $index => $gal): ?>
                                        <div class="form-gallery-item" draggable="true" style="position: relative; width: 130px; border: 1px solid var(--gray-200); border-radius: 8px; overflow: hidden; cursor: grab;" data-id="<?= $gal['id'] ?>">
                                            <input type="hidden" name="gallery_sort_order[]" value="<?= $gal['id'] ?>">
                                            
                                            <img src="<?php echo ROOT_URL ?>/uploads/projects/gallery/<?= $gal['image'] ?>" style="width: 100%; height: 90px; object-fit: cover; display: block;">
                                            
                                            <div class="form-gallery-meta" style="background: var(--gray-100); text-align: center; font-size: 0.85rem; padding: 6px; font-weight: 600; color: var(--navy);">
                                                <span>Ảnh #<?= $index + 1 ?></span>
                                            </div>
                                            
                                            <button type="button" onclick="removeProjectGallery(this, <?= $gal['id'] ?>)" title="Xóa ảnh này" style="position: absolute; top: 5px; right: 5px; background: rgba(220, 38, 38, 0.9); color: white; border: none; border-radius: 4px; width: 26px; height: 26px; cursor: pointer;">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="color: var(--gray-400); font-style: italic;">Chưa có ảnh phụ nào.</p>
                                <?php endif; ?>
                            </div>
                            
                            <div id="deleted-pj-gallery-inputs"></div> 

                            <div class="form-group" style="margin-top: 15px;">
                                <input type="file" name="gallery[]" multiple class="form-control" accept="image/*">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Dự án tiêu biểu (Hiện trang chủ)</label>
                            <select name="is_featured" class="form-control">
                                <option value="0" <?= $pj['is_featured'] == 0 ? 'selected' : '' ?>>Bình thường</option>
                                <option value="1" <?= $pj['is_featured'] == 1 ? 'selected' : '' ?>>Nổi bật / Tiêu biểu</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Trạng thái hiển thị</label>
                            <select name="status" class="form-control">
                                <option value="1" <?= $pj['status'] == 1 ? 'selected' : '' ?>>Hiển thị ngay</option>
                                <option value="0" <?= $pj['status'] == 0 ? 'selected' : '' ?>>Lưu bản nháp (Ẩn)</option>
                            </select>
                        </div>
                    </div>
                </div><br>

                <div class="form-group">
                    <label><strong>Nội dung chi tiết dự án (Quá trình thi công, vật tư, hiệu quả...)</strong></label>
                    <textarea name="content" id="content" class="form-control news-content-editor" rows="15"><?= htmlspecialchars($pj['content']) ?></textarea>
                </div>
                
                <div class="mt-4" style="border-top: 1px solid #e2e8f0; padding-top: 20px; text-align: right;">
                    <a href="index.php?page=manage-projects" class="btn btn-outline">Hủy bỏ</a>
                    <button type="submit" name="btn_edit_project" class="btn btn-primary" style="padding: 12px 25px; font-size: 16px;">
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
<script>
    // 1. Hàm Xóa ảnh (Ẩn đi và đưa vào danh sách chờ xóa trên Server)
    function removeProjectGallery(btn, galId) {
        if(confirm('Bạn có chắc muốn xóa ảnh này khỏi dự án?')) {
            let item = btn.closest('.form-gallery-item');
            item.style.display = 'none'; // Ẩn khỏi màn hình
            item.innerHTML = ''; // Hủy input sort_order bên trong
            
            // Gắn ID vào danh sách án tử hình
            document.getElementById('deleted-pj-gallery-inputs').innerHTML += `<input type="hidden" name="delete_gallery[]" value="${galId}">`;
            updatePjGalleryIndexes();
        }
    }

    // 2. Hàm đánh lại số thứ tự "Ảnh #1, Ảnh #2"
    function updatePjGalleryIndexes() {
        let items = document.querySelectorAll('#project-gallery-container .form-gallery-item:not([style*="display: none"])');
        items.forEach((item, index) => {
            let span = item.querySelector('.form-gallery-meta span');
            if(span) span.innerText = 'Ảnh #' + (index + 1);
        });
    }

    // 3. Logic Kéo Thả (Drag & Drop)
    const pjGalleryContainer = document.getElementById('project-gallery-container');
    let draggedPjItem = null;

    if (pjGalleryContainer) {
        pjGalleryContainer.addEventListener('dragstart', (e) => {
            if (e.target.closest('.form-gallery-item')) {
                draggedPjItem = e.target.closest('.form-gallery-item');
                setTimeout(() => draggedPjItem.style.opacity = '0.4', 0);
            }
        });

        pjGalleryContainer.addEventListener('dragend', (e) => {
            if (draggedPjItem) {
                draggedPjItem.style.opacity = '1';
                draggedPjItem = null;
                updatePjGalleryIndexes(); // Kéo xong đánh số lại
            }
        });

        pjGalleryContainer.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (!draggedPjItem) return;
            const afterElement = [...pjGalleryContainer.querySelectorAll('.form-gallery-item:not([style*="display: none"])')]
                .find(child => e.clientX <= child.getBoundingClientRect().left + child.offsetWidth / 2);
            
            if (afterElement) {
                pjGalleryContainer.insertBefore(draggedPjItem, afterElement);
            } else {
                pjGalleryContainer.appendChild(draggedPjItem);
            }
        });
    }
</script>