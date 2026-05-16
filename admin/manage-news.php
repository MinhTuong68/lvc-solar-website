<?php
    include_once("../classes/news.php");
    $news_obj = new News($conn);
    if (isset($_GET['action']) && $_GET['action'] == 'toggle_status' && isset($_GET['id'])) {
        $news_id = (int)$_GET['id'];
        $current_val = (int)$_GET['current']; 
        $new_status = ($current_val == 1) ? 0 : 1;

        $stmt = $conn->prepare("UPDATE tbl_news SET status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $new_status, $news_id);
            $stmt->execute();
        }
        header("Location: index.php?page=manage-news");
        exit();
    }
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $current_search = isset($_GET['search']) ? trim($_GET['search']) : '';
    // Bắt đầu lọc dữ liệu
    $sql = "SELECT * FROM tbl_news WHERE 1=1";

    if ($status !== 'all' && $status !== '') {
        $status_filter = (int)$status;
        $sql .= " AND status = $status_filter";
    }

    if ($current_search !== '') {
        $search_filter = mysqli_real_escape_string($conn, $current_search);
        $sql .= " AND title LIKE '%$search_filter%'";
    }

    $sql .= " ORDER BY id DESC";
    
    // Đẩy dữ liệu đã lọc vào biến $allNews để vòng lặp ở dưới chạy bình thường
    $res = mysqli_query($conn, $sql);
    $allNews = [];
    if($res && mysqli_num_rows($res) > 0){
        while($row = mysqli_fetch_assoc($res)) {
            $allNews[] = $row;
        }
    }
?>

<div class="wrapper">
    <div class="cs-page-wrapper">
        <div class="cs-header">
            <h1>Bài viết Blog & SEO</h1>
            <span style="color: #64748b; font-size: 14px;">Nội dung / </span>
            <span style="font-size: 14px; font-weight: bold;">Blog (SEO)</span>
        </div>

        <div class="cs-filter-bar">
            <form action="" method="GET" class = "filter-bar">
                <input type="hidden" name="page" value="manage-news">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                <a href="?page=manage-add-news" class="cs-btn-addservices"><i class="fa-solid fa-plus"></i> Thêm Blog mới</a>
                <input type="text" name="search" class="cs-select" placeholder="🔍 Tìm tiêu đề bài viết..." value="<?php echo htmlspecialchars($current_search ?? ''); ?>">
                <button type="submit" class="cs-btn-filter"><i class="fa-solid fa-filter"></i> Lọc</button>
                <a href="index.php?page=manage-news" class="cs-btn-clear">Xóa</a>
            </form>
        </div>

        <div class="cs-table-container">
            <table class="cs-table">
                <thead>
                    <tr>
                        <th width="">STT</th>
                        <th width="" class="text-center">Hình ảnh</th>
                        <th>Tiêu đề</th>
                        <th width="">Ngày đăng</th>
                        <th width="">Trạng thái</th>
                        <th width="">hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if(!empty($allNews)){
                            $stt=1;
                            foreach($allNews as $rows){
                                $id = $rows['id'];
                                $image = $rows['image'];
                                $title = $rows['title'];
                                $created_at = $rows['created_at'];
                                $status = $rows['status'];
                                $eye_icon = ($status == 1) ? 'fa-eye' : 'fa-eye-slash';
                                $badge_class = ($status == 1) ? 'status-active' : 'status-hidden';
                                $badge_text = ($status == 1) ? 'Hoạt động' : 'Tạm ẩn';
                                $status_html = '
                                <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                    <a href="index.php?page=manage-news&action=toggle_status&id='.$id.'&current='.$status.'" style="color: #64748b; font-size: 1.1rem;" title="Đổi trạng thái">
                                        <i class="fa-solid '.$eye_icon.'"></i>
                                    </a>
                                    <span class="status-badge '.$badge_class.'">'.$badge_text.'</span>
                                </div>';

                                ?>
                                    <tr>
                                        <td>#<?php echo $stt++ ?></td>
                                        <td class="text-center">
                                            <div style="width: 80px; height: 50px; background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; padding: 2px;">
                                                <img src="../uploads/news/images/<?php echo $image ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;" alt="Logo">
                                            </div>
                                        </td>
                                        <td><?php echo $title ?></td>
                                        <td><?php echo $created_at ?></td>
                                        <td><?php echo $status_html ?></td>
                                        <td>
                                            <a href="index.php?page=edit-news&id=<?= $id ?>" class="cs-btn cs-btn-view" title="Xem & Cập nhật"><i class="fa-solid fa-eye"></i></a>
                                            <a href="actions/delete-news.php?id=<?php echo $id; ?>" 
                                                class="cs-btn cs-btn-delete" 
                                                title="Xóa"
                                                onclick="event.preventDefault(); let urlXoa = this.href; openModal('Xác nhận xóa bài viết?', 'Bạn có chắc chắn muốn xóa bài viết này không? Toàn bộ nội dung và hình ảnh của bài viết sẽ bị xóa vĩnh viễn khỏi hệ thống!', 'fa-solid fa-trash-can', 'Xóa bài viết', function() { window.location.href = urlXoa; })">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php
                            }
                        }else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 30px; color: #64748b;'>Không có bài viết nào.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>