<?php
    include('../classes/categories.php');
    $categoryManager = new Category($conn);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_add_category'])){
        $name = trim($_POST['category_name']);
        $slug = trim($_POST['category_slug']);
        $status = $_POST['status'];

        $result = $categoryManager->addCategory($name, $slug, $status);
        if ($result == "success") {
            $_SESSION['toast_message'] = "Thêm danh mục thành công!";
            $_SESSION['toast_type'] = 'success';      
        } else if ($result == "exists") {
            $_SESSION['toast_message'] = "Lỗi: Đường dẫn (Slug) này đã tồn tại!";
            $_SESSION['toast_type'] = 'error';    
        } else {
            $_SESSION['toast_message'] = "Lỗi hệ thống, vui lòng thử lại!";
            $_SESSION['toast_type'] = 'error';  
        }
        header("Location: index.php?page=manage-category"); 
        exit();
        
    }
    $listCategories = $categoryManager->getAllCategories();

    // --- BẮT ĐẦU CÀI ĐẶT PHÂN TRANG ---
    $limit = 5; // Số danh mục hiển thị trên 1 trang (bạn có thể đổi thành 10, 20)
    
    // Lấy số trang hiện tại trên URL (vd: ?page=manage-category&p=2). Nếu không có thì mặc định là 1
    $current_p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    if ($current_p < 1) $current_p = 1;

    // Tính toán bỏ qua bao nhiêu dòng
    $offset = ($current_p - 1) * $limit;

    // Đếm tổng số danh mục và tính ra tổng số trang
    $total_records = $categoryManager->getTotalCategories();
    $total_pages = ceil($total_records / $limit); // ceil để làm tròn lên (vd: 11 bài / 5 = 2.2 -> 3 trang)

    // Lấy dữ liệu cho trang hiện tại
    $listCategories = $categoryManager->getCategoriesPaginated($limit, $offset);
    // --- KẾT THÚC PHÂN TRANG ---
?>

<div class="wrapper">
    <div class="page-header-add">
        <h2 class="page-title-add">QUẢN LÝ DOANH MỤC SẢN PHẨM</h2>
    </div><br>

    <form action="" method="POST" id="addCategoryForm">
        <div class="form-layout-add2">
            <div class="form-col-right-add">
                <div class="form-panel-add">
                    <h3 class="panel-title-add"><i class="fa-solid fa-plus"></i> THÊM DOANH MỤC MỚI</h3><br>

                    <div class="form-group-add">
                        <label class="form-label-add">Tên doanh mục *</label>
                        <input type="text" id="category_name" name="category_name" class="form-control-add" required placeholder="VD: Tấm Pin Solar">
                        <span class="error-msg" id="error_category_name"></span>
                    </div>

                    <div class="form-group-add">
                        <label class="form-label-add">Đường dẫn (Slug) *</label>
                        <input id="category_slug" type="text" name="category_slug" class="form-control-add" required placeholder="VD: tam-pin-solar">
                        <span class="error-msg" id="slug_category_error"></span>
                    </div>

                    <div class="form-group-add">
                        <label class="form-label-add">Trạng thái</label>
                        <select name="status" class="form-control-add">
                            <option value="1">Đang hoạt động (Công khai)</option>
                            <option value="0">Tạm ẩn (Nháp)</option>
                        </select>
                    </div>

                    <button type="submit" name="btn_add_category" id="btn-submit-category" class="btn-submit" style="margin-top: 10px;">
                        <i class="fa-solid fa-save"></i> Lưu doanh mục
                    </button>
                </div>
            </div>


            <div class="form-col-left-add">
                <div class="form-panel-add">
                     <h3 class="panel-title-add"><i class="fa-solid fa-list"></i> DANH SÁCH DOANH MỤC</h3><br>
                     <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th class="text-center">TÊN DOANH MỤC</th>
                                    <th class="text-center">ĐƯỜNG DẪN (SLUG)</th>
                                    <th class="text-center">TRẠNG THÁI</th>
                                    <th class="text-center">HÀNH ĐỘNG</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if (!empty($listCategories)){
                                        $stt = 1;
                                        foreach ($listCategories as $cat){
                                            // Tạo màu trạng thái bằng class
                                            if ($cat['status'] == 1) {
                                                $status_html = '<span class="status-badge status-active">Đang hoạt động</span>';
                                            } else {
                                                $status_html = '<span class="status-badge status-hidden">Tạm ẩn</span>';
                                            }
                                            ?>
                                                <tr>
                                                    <td><?= $stt++ ?></td>
                                                    <td class="fw-bold text-center"><?= $cat['name'] ?></td>
                                                    <td class="text-center"><?= $cat['slug'] ?></td>
                                                    <td class="text-center"><?= $status_html ?></td>
                                                    <td class="text-center">
                                                        <a href="index.php?page=edit-category&id=<?= $cat['id'] ?>" class="action-btn btn-edit" title="Sửa"><i class="fa-solid fa-pen-to-square"></i></a>
                                                        <a href="javascript:void(0)" class="action-btn btn-delete" title="Xóa" onclick="openModal(<?= $cat['id'] ?>, 'category')">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php
                                        }
                                    }
                                    else{
                                        ?>
                                            <tr>
                                                <td colspan="5" class="empty-msg">Chưa có danh mục nào!</td>
                                            </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                     </table>
                     <?php if ($total_pages > 1): ?>
                        <div class="pagination-container">
                            <span class="page-info">(<?= $current_p ?>/<?= $total_pages ?> trang)</span>
                            <div class="pagination">
                                <a href="?page=manage-category&p=<?= ($current_p > 1) ? ($current_p - 1) : 1 ?>" class="page-link <?= ($current_p <= 1) ? 'disabled' : '' ?>">
                                    <i class="fa-solid fa-angles-left"></i>
                                </a>

                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=manage-category&p=<?= $i ?>" class="page-link <?= ($current_p == $i) ? 'active' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>

                                <a href="?page=manage-category&p=<?= ($current_p < $total_pages) ? ($current_p + 1) : $total_pages ?>" class="page-link <?= ($current_p >= $total_pages) ? 'disabled' : '' ?>">
                                    <i class="fa-solid fa-angles-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
</div>