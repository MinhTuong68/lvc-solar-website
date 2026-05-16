<?php
    include("../classes/projects.php");
    $project_obj = new Project($conn);

    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $current_search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $sql = "SELECT * FROM tbl_projects WHERE 1=1";

    if ($status !== 'all' && $status !== '') {
        $status_filter = (int)$status;
        $sql .= " AND status = $status_filter";
    }

    if ($current_search !== '') {
        $search_filter = mysqli_real_escape_string($conn, $current_search);
        $sql .= " AND (name LIKE '%$search_filter%' OR client LIKE '%$search_filter%' OR location LIKE '%$search_filter%')";
    }

    // Sắp xếp: Nổi bật lên trước, mới nhất lên trước
    $sql .= " ORDER BY is_featured DESC, id DESC";
    
    // Đẩy dữ liệu đã lọc vào biến $allProjects để vòng lặp bên dưới chạy bình thường
    $res = mysqli_query($conn, $sql);
    $allProjects = [];
    if($res && mysqli_num_rows($res) > 0){
        while($row = mysqli_fetch_assoc($res)) {
            $allProjects[] = $row;
        }
    }

    if (isset($_GET['action']) && $_GET['action'] == 'toggle_status' && isset($_GET['id'])) {
        $project = (int)$_GET['id'];
        $current_status = (int)$_GET['current'];
        
        // Dùng if - else rành mạch, dễ hiểu
        $new_status = 0;
        if ($current_status == 1) {
            $new_status = 0; // Nếu đang là 1 (Hiện) thì đổi thành 0 (Ẩn)
        } else {
            $new_status = 1; // Nếu đang là 0 (Ẩn) thì đổi thành 1 (Hiện)
        }

        // Cập nhật vào CSDL
        $stmt = $conn->prepare("UPDATE tbl_projects SET status = ? WHERE id = ?");
        
        if ($stmt) {
            $stmt->bind_param("ii", $new_status, $project);
            $stmt->execute();
        }
        header("Location: index.php?page=manage-projects");
        exit();
    }

    // --- BẮT SỰ KIỆN ĐỔI TRẠNG THÁI NỔI BẬT ---
    if (isset($_GET['action']) && $_GET['action'] == 'toggle_featured' && isset($_GET['id'])) {
        $update_id = (int)$_GET['id'];
        $current_featured = (int)$_GET['current'];
        
        // Gọi hàm toggle trong class
        $project_obj->toggleFeatured($update_id, $current_featured);
        
        $_SESSION['toast_message'] = "Đã cập nhật trạng thái nổi bật của dự án!";
        $_SESSION['toast_type'] = "success";
        header("Location: index.php?page=manage-projects");
        exit;
    }
?>
<div class="wrapper">
    <div class="cs-page-wrapper">
         <div class="cs-header">
            <h1>Bài viết dự án</h1>
            <span style="color: #64748b; font-size: 14px;">Nội dung / </span>
            <span style="font-size: 14px; font-weight: bold;">Dự án</span>
        </div>

        <div class="cs-filter-bar">
            <form action="" method="GET" class = "filter-bar">
                <input type="hidden" name="page" value="manage-projects">
                <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                <a href="?page=manage-add-projects" class="cs-btn-addservices"><i class="fa-solid fa-plus"></i> Thêm dự án mới</a>
                <input type="text" name="search" class="cs-select" placeholder="🔍 Tìm khách hàng, SĐT..." value="<?php echo htmlspecialchars($current_search); ?>">
                <button type="submit" class="cs-btn-filter"><i class="fa-solid fa-filter"></i> Lọc</button>
                <a href="index.php?page=manage-projects" class="cs-btn-clear">Xóa</a>
            </form>
        </div>

        <div class="cs-table-container">
            <table class="cs-table">
                <thead>
                     <tr>
                        <th>STT</th>
                        <th>Hình ảnh</th>
                        <th>Thông tin dự án</th>
                        <th>Thông số & Vị trí</th>
                        <th>tiêu đề</th>
                        <th class="text-center">Nổi bật</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if (!empty($allProjects)){
                            $stt = 1;
                            foreach ($allProjects as $p){
                                $check_prod = $conn->prepare("SELECT COUNT(id) FROM tbl_projects WHERE id = ?");
                                $check_prod->bind_param("i", $p['id']);
                                $check_prod->execute();
                                $product_count = $check_prod->get_result()->fetch_row()[0];

                                $eye_icon = ($p['status'] == 1) ? 'fa-eye' : 'fa-eye-slash';
                                $badge_class = ($p['status'] == 1) ? 'status-active' : 'status-hidden';
                                $badge_text = ($p['status'] == 1) ? 'Hoạt động' : 'Tạm ẩn';
                                $status_html = '
                                <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                    <a href="index.php?page=manage-projects&action=toggle_status&id='.$p['id'].'&current='.$p['status'].'" style="color: #64748b; font-size: 1.1rem;" title="Đổi trạng thái">
                                        <i class="fa-solid '.$eye_icon.'"></i>
                                    </a>
                                    <span class="status-badge '.$badge_class.'">'.$badge_text.'</span>
                                </div>';
                                ?>  
                                    <tr>
                                        <td>#<?php echo $stt++ ?></td>
                                        <td>
                                            <img src="<?php echo ROOT_URL?>uploads/projects/images/<?= $p['image'] ?>" alt="Ảnh dự án" style="width: 80px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        </td>
                                        <td>
                                            <div class="project-title"><?= $p['name'] ?></div>
                                            <div class="project-meta">
                                                <i class="fa-solid fa-user-tie"></i> Khách hàng: <?= $p['client'] != '' ? $p['client'] : 'Đang cập nhật' ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="project-capacity">
                                                <i class="fa-solid fa-bolt icon-amber"></i> <?= $p['capacity'] != '' ? $p['capacity'] : '--' ?>
                                            </div>
                                            <div class="project-meta">
                                                <i class="fa-solid fa-location-dot"></i> <?= $p['location'] != '' ? $p['location'] : 'Chưa cập nhật' ?>
                                            </div>
                                        </td>
                                       <td>
                                            <p><?php echo $p['description']?></p>
                                       </td>
                                       <td class="text-center">
                                            <?php 
                                                // Quyết định biểu tượng: Sao đặc (nếu nổi bật) hoặc Sao rỗng (nếu bình thường)
                                                $star_icon = ($p['is_featured'] == 1) ? 'fa-solid fa-star' : 'fa-regular fa-star';
                                                // Quyết định màu sắc: Vàng amber (nếu nổi bật) hoặc Xám (nếu bình thường)
                                                $star_color = ($p['is_featured'] == 1) ? '#f59e0b' : '#94a3b8';
                                            ?>
                                            <a href="index.php?page=manage-projects&action=toggle_featured&id=<?= $p['id'] ?>&current=<?= $p['is_featured'] ?>" 
                                            style="color: <?= $star_color ?>; font-size: 1.25rem;" 
                                            title="Bật/Tắt dự án tiêu biểu">
                                                <i class="<?= $star_icon ?>"></i>
                                            </a>
                                        </td>
                                       <td class="text-center"><?= $status_html ?></td>
                                        <td class="text-center">
                                            <div class="action-btns">
                                                <a href="index.php?page=edit-project&id=<?= $p['id']  ?>" class="btn-icon btn-edit-icon" title="Sửa"><i class="fa-solid fa-pen-to-square"></i></a>
                                                <a href="actions/delete-project.php?type=project&id=<?= $p['id'] ?>" class="btn-icon btn-delete-icon" title="Xóa" onclick="event.preventDefault(); let urlXoa = this.href; openModal('Xác nhận xóa dự án?', 'Bạn có chắc chắn muốn xóa dự án này không? Toàn bộ dữ liệu và hình ảnh logo của dự án sẽ bị xóa vĩnh viễn khỏi hệ thống!', 'fa-solid fa-trash', 'Xóa dự án', function() { window.location.href = urlXoa; })">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>        
                                        </td>
                                    </tr> 
                                <?php
                            }
                        }else {
                        echo "<tr><td colspan='8' style='text-align:center; padding: 40px; color: #64748b;'><i class='fa-solid fa-folder-open' style='font-size: 40px; color: #cbd5e1; margin-bottom: 10px; display: block;'></i>Chưa có dự án nào.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>