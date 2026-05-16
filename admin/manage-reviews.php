<?php
    require_once '../classes/reviews.php';
    $reviewObj = new Review($conn);

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        if (isset($_POST['action'])) {
            $action = $_POST['action'];
            
            if ($action == 'delete' && isset($_POST['id'])) {
                $reviewObj->deleteReview($_POST['id']);
                $_SESSION['toast_message'] = "Đã xóa đánh giá và hình ảnh kèm theo thành công!";
                $_SESSION['toast_type'] = 'success';
                echo "<script>window.location.href='index.php?page=manage-reviews';</script>";
                exit;
            }
            
            if ($action == 'toggle' && isset($_POST['id']) && isset($_POST['status'])) {
                $reviewObj->toggleStatus($_POST['id'], $_POST['status']);
                $_SESSION['toast_message'] = "Cập nhật trạng thái hiển thị thành công!";
                $_SESSION['toast_type'] = 'success';
                echo "<script>window.location.href='index.php?page=manage-reviews';</script>";
                exit;
            }
        }

        if (isset($_POST['bulk_delete_stars'])) {
            $max_star = $_POST['max_star_delete'];
            $reviewObj->bulkDeleteByStar($max_star);
            $_SESSION['toast_message'] = "Đã dọn dẹp sạch sẽ các đánh giá từ 1 đến $max_star sao!";
            $_SESSION['toast_type'] = 'success';
            echo "<script>window.location.href='index.php?page=manage-reviews';</script>";
            exit;
        }
    }

    $config_file = 'review_settings.json';

    // 1. XỬ LÝ LƯU CẤU HÌNH KHI BẤM NÚT
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_allowed_stars'])) {
        $stars_to_save = isset($_POST['stars']) ? $_POST['stars'] : [];
        file_put_contents($config_file, json_encode($stars_to_save));
        
        $_SESSION['toast_message'] = "Đã lưu cấu hình sao cho phép!";
        $_SESSION['toast_type'] = 'success';
        echo "<script>window.location.href='index.php?page=manage-reviews';</script>";
        exit;
    }

    // 2. ĐỌC CẤU HÌNH TỪ FILE ĐỂ HIỂN THỊ
    $allowed_stars_arr = [];
    if (file_exists($config_file)) {
        $allowed_stars_arr = json_decode(file_get_contents($config_file), true);
    }

// 2. THỐNG KÊ SỐ LƯỢNG ĐỂ LÀM BỘ LỌC SAO
$counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$res_counts = $conn->query("SELECT rating, COUNT(*) as cnt FROM tbl_reviews GROUP BY rating");
while($row = $res_counts->fetch_assoc()) {
    $counts[$row['rating']] = $row['cnt'];
}
$total_reviews = array_sum($counts);
$res_report_count = $conn->query("SELECT COUNT(DISTINCT review_id) as report_total FROM tbl_review_reports");
$total_reported = 0;
if($res_report_count) {
    $total_reported = $res_report_count->fetch_assoc()['report_total'];
}

// 3. TRUY VẤN DANH SÁCH ĐÁNH GIÁ (Có kèm thông tin Sản phẩm và Tố cáo)
$star_filter = isset($_GET['star']) ? (int)$_GET['star'] : 0;
$filter_type = isset($_GET['filter']) ? $_GET['filter'] : '';
$where_sql = "";
$having_sql = "";

if ($star_filter > 0 && $star_filter <= 5) {
    $where_sql = "WHERE r.rating = $star_filter";
}
elseif ($filter_type == 'reported') {
    $having_sql = "HAVING report_count > 0"; // MỚI THÊM: Nếu bấm nút Bị báo cáo thì gán điều kiện này
}

$sql = "SELECT r.*, p.name as product_name, p.image as product_image, 
        COUNT(rp.id) as report_count, 
        GROUP_CONCAT(rp.reason SEPARATOR ' | ') as report_reasons
        FROM tbl_reviews r
        JOIN tbl_products p ON r.product_id = p.id
        LEFT JOIN tbl_review_reports rp ON r.id = rp.review_id
        $where_sql
        GROUP BY r.id
        $having_sql
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);
?>

<div class="wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-star-half-stroke"></i> Quản lý Đánh giá & Tố cáo</h2>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="star-filter-container">
        <a href="index.php?page=manage-reviews" class="star-filter-btn <?= $star_filter == 0 ? 'active' : '' ?> all-stars">
            <i class="fa-solid fa-layer-group"></i> Tất cả (<?= $total_reviews ?>)
        </a>
        <a href="index.php?page=manage-reviews&star=5" class="star-filter-btn <?= $star_filter == 5 ? 'active' : '' ?>">
            <i class="fa-solid fa-star"></i> 5 Sao (<?= $counts[5] ?>)
        </a>
        <a href="index.php?page=manage-reviews&star=4" class="star-filter-btn <?= $star_filter == 4 ? 'active' : '' ?>">
            <i class="fa-solid fa-star"></i> 4 Sao (<?= $counts[4] ?>)
        </a>
        <a href="index.php?page=manage-reviews&star=3" class="star-filter-btn <?= $star_filter == 3 ? 'active' : '' ?>">
            <i class="fa-solid fa-star"></i> 3 Sao (<?= $counts[3] ?>)
        </a>
        <a href="index.php?page=manage-reviews&star=2" class="star-filter-btn <?= $star_filter == 2 ? 'active' : '' ?>">
            <i class="fa-solid fa-star"></i> 2 Sao (<?= $counts[2] ?>)
        </a>
        <a href="index.php?page=manage-reviews&star=1" class="star-filter-btn <?= $star_filter == 1 ? 'active' : '' ?>">
            <i class="fa-solid fa-star"></i> 1 Sao (<?= $counts[1] ?>)
        </a>
        <a href="index.php?page=manage-reviews&filter=reported" class="star-filter-btn <?= $filter_type == 'reported' ? 'active' : '' ?>" style="color: <?= $filter_type == 'reported' ? '#dc2626' : '#ef4444' ?>; border-color: <?= $filter_type == 'reported' ? '#f87171' : '#fca5a5' ?>; background: <?= $filter_type == 'reported' ? '#fef2f2' : '#fff' ?>;">
            <i class="fa-solid fa-flag"></i> Bị báo cáo (<?= $total_reported ?>)
        </a>
    </div>

    <div style="display: flex; gap: 10px; padding: 10px;">
        <div class="bulk-delete-box mb-4">
            <div>
                <strong><i class="fa-solid fa-broom"></i> Dọn dẹp đánh giá:</strong><br>
                <span style="font-size: 13px; color: #dc2626;">Thao tác này sẽ xóa vĩnh viễn đánh giá khỏi CSDL.</span>
            </div>
            <form method="POST" onsubmit="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn xóa hàng loạt không? Hành động này không thể hoàn tác!');" style="display:flex; gap:10px;">
                <select name="max_star_delete" class="form-control" style="width: 250px;">
                    <option value="1">Xóa tất cả đánh giá 1 Sao</option>
                    <option value="2">Xóa tất cả đánh giá từ 1 ➔ 2 Sao</option>
                    <option value="3">Xóa tất cả đánh giá từ 1 ➔ 3 Sao</option>
                    <option value="4">Xóa tất cả đánh giá từ 1 ➔ 4 Sao</option>
                </select>
                <button type="submit" name="bulk_delete_stars" class="btn btn-danger">Xóa ngay</button>
            </form>
        </div>

        <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 20px; border-radius: 10px; flex: 1;">
            <div style="margin-bottom: 15px;">
                <strong style="color: #d97706; font-size: 15px;"><i class="fa-solid fa-sliders"></i> Cấu hình Sao cho phép đánh giá:</strong><br>
                <span style="font-size: 13px; color: #b45309;">Bật/tắt từng ngôi sao để giới hạn quyền đánh giá của khách.</span>
            </div>
            
            <form method="POST" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0;">
                <div style="display: flex; gap: 15px;">
                    <?php 
                    for($i=1; $i<=5; $i++): 
                        $is_active = in_array((string)$i, $allowed_stars_arr);
                    ?>
                        <label style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px;">
                            <input type="checkbox" name="stars[]" value="<?= $i ?>" <?= $is_active ? 'checked' : '' ?> 
                                style="display: none;" 
                                onchange="this.nextElementSibling.style.color = this.checked ? '#f59e0b' : '#cbd5e1'">
                            
                            <i class="fa-solid fa-star" style="font-size: 28px; color: <?= $is_active ? '#f59e0b' : '#cbd5e1' ?>; transition: color 0.3s;"></i>
                            <span style="font-size: 13px; font-weight: 700; color: #64748b;"><?= $i ?> Sao</span>
                        </label>
                    <?php endfor; ?>
                </div>
                
                <button type="submit" name="update_allowed_stars" class="btn btn-add" style="padding: 10px 20px; border: none; cursor: pointer; font-weight: 600;">
                    Lưu cấu hình
                </button>
            </form>
        </div>
    </div>
    <br>

    <div class="rv-table-wrapper">
        <table class="rv-table">
            <thead>
                <tr>
                    <th>Sản phẩm & ID</th>
                    <th>Khách hàng</th>
                    <th>Đánh giá</th>
                    <th>Nội dung</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="width: 250px;">
                            <div class="rv-product-info">
                                <img src="<?= ROOT_URL ?>uploads/products/images/<?= $row['product_image'] ?>" alt="" style="width:50px; height:50px; object-fit:cover; border-radius:5px;">
                                <div>
                                    <a href="../index.php?page=detail_product&id=<?= $row['product_id'] ?>" target="_blank" class="review-product-link">
                                        <?= htmlspecialchars($row['product_name']) ?>
                                    </a>
                                    <div class="rv-product-id">Mã SP: #<?= $row['product_id'] ?></div>
                                </div>
                            </div>
                        </td>
                        
                        <td>
                            <strong class="rv-customer-name"><?= htmlspecialchars($row['customer_name']) ?></strong><br>
                            <span class="rv-customer-email"><?= htmlspecialchars($row['customer_email']) ?></span><br>
                            <span class="rv-review-date"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></span>
                        </td>
                        
                        <td>
                            <div class="rv-star-rating">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <?= $i <= $row['rating'] ? '<i class="fa-solid fa-star rv-star-filled"></i>' : '<i class="fa-regular fa-star rv-star-empty"></i>' ?>
                                <?php endfor; ?>
                            </div>
                            <?php if($row['report_count'] > 0): ?>
                                <span class="report-badge" title="Lý do: <?= htmlspecialchars($row['report_reasons']) ?>">
                                    <i class="fa-solid fa-flag"></i> Bị tố cáo (<?= $row['report_count'] ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <div class="review-content-box">
                                <?= nl2br(htmlspecialchars($row['content'])) ?>
                                
                                <?php if(!empty($row['images'])): ?>
                                    <div style="margin-top: 10px; display: flex; gap: 8px; flex-wrap: wrap;">
                                        <?php 
                                        // Tách các tên ảnh bằng dấu phẩy
                                        $review_images = explode(',', $row['images']);
                                        foreach($review_images as $img_name): 
                                            $img_name = trim($img_name);
                                            if(!empty($img_name)):
                                        ?>
                                            <a href="<?= ROOT_URL ?>uploads/reviews/<?= htmlspecialchars($img_name) ?>" target="_blank" title="Click để xem ảnh lớn">
                                                <img src="<?= ROOT_URL ?>uploads/products/reviews/<?= htmlspecialchars($img_name) ?>" 
                                                    alt="Ảnh đánh giá" 
                                                    style="width: 45px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #cbd5e1; transition: transform 0.2s;"
                                                    onmouseover="this.style.transform='scale(1.1)'" 
                                                    onmouseout="this.style.transform='scale(1)'">
                                            </a>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="status" value="<?= $row['status'] ?>">
                                <button type="submit" class="rv-btn <?= $row['status'] == 1 ? 'rv-btn-hide' : 'rv-btn-show' ?>" 
                                        title="<?= $row['status'] == 1 ? 'Ẩn đánh giá này' : 'Cho phép hiển thị lại' ?>">
                                    <i class="fa-solid <?= $row['status'] == 1 ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                                </button>
                            </form>
                        </td>
                        
                        <td>
                            <div class="rv-action-group">
                                
                                <form method="POST" onsubmit="return confirm('Xóa vĩnh viễn đánh giá này và ảnh đính kèm?');" style="margin: 0;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn-icon btn-delete-icon" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="rv-empty-state">
                            <i class="fa-regular fa-comments"></i><br>
                            Chưa có đánh giá nào phù hợp.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>