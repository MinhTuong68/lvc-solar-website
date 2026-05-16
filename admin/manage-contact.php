<?php
    include("../classes/contact.php");
    $contact_obj = new Contact($conn);

    // Bổ sung khai báo các biến để tránh lỗi Undefined variable
    $current_status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $current_search = isset($_GET['search']) ? trim($_GET['search']) : '';

    if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id'])) {
        $update_id = (int)$_GET['id'];
        $new_status = (int)$_GET['new_status'];
        mysqli_query($conn, "UPDATE tbl_contacts SET status = $new_status WHERE id = $update_id");
        header("Location: index.php?page=manage-contact");
        exit;
    }
?>
<div class="wrapper">
    <div class="cs-page-wrapper">
        <div class="cs-header">
            <h1>Quản lý yêu cầu báo giá & dịch vụ</h1>
            <span style="color: #64748b; font-size: 14px;">Nội dung / </span>
            <span style="font-size: 14px; font-weight: bold;">Liên hệ</span>
        </div>

        <div class="cs-tabs">
            <a href="index.php?page=manage-contact&status=all" class="cs-tab <?= ($current_status == 'all') ? 'active' : '' ?>">Tất cả</a>
            <a href="index.php?page=manage-contact&status=0" class="cs-tab <?= ($current_status == '0') ? 'active' : '' ?>">Chưa đọc</a>
            <a href="index.php?page=manage-contact&status=1" class="cs-tab <?= ($current_status == '1') ? 'active' : '' ?>">Đã liên hệ</a>
        </div>

        <div class="cs-filter-bar">
            <!-- Đổi action sang manage-contact -->
            <form action="" method="GET" class="filter-bar" style="display: flex; gap: 10px; align-items: center; width: 100%;">
                <input type="hidden" name="page" value="manage-contact">
                
                <?php if ($current_status != 'all') { ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($current_status) ?>">
                <?php } ?>
                
                <input type="text" name="search" class="cs-select" placeholder="🔍 Tìm khách hàng, SĐT..." value="<?= htmlspecialchars($current_search) ?>" style="min-width: 250px;">
                
                <button type="submit" class="cs-btn-filter"><i class="fa-solid fa-filter"></i> Lọc</button>
                <a href="index.php?page=manage-contact" class="cs-btn-clear">Xóa lọc</a>
            </form>
        </div>
        
        
        <div class="cs-header">
            <h1>Quản lý tin nhắn liên hệ</h1>
            <span style="color: #64748b; font-size: 14px;">Nội dung / Liên hệ</span>
        </div>

        <div class="cs-table-container">
            <table class="cs-table">
                <thead>
                    <tr>
                        <th style="width: 5%">STT</th>
                        <th style="width: 5%">ID</th>
                        <th style="width: 25%">Khách hàng</th>
                        <th style="width: 33%">Nội dung tin nhắn</th>
                        <th style="width: 12%">Trạng thái</th>
                        <th style="width: 20%; text-align: right;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        // 1. XÂY DỰNG CÂU LỆNH SQL ĐỘNG ĐỂ LỌC VÀ TÌM KIẾM
                        $sql = "SELECT * FROM tbl_contacts WHERE 1=1";

                        // Lọc theo Tab (Tất cả / Chưa đọc / Đã liên hệ)
                        if ($current_status !== 'all' && $current_status !== '') {
                            $status_filter = (int)$current_status;
                            $sql .= " AND status = $status_filter";
                        }

                        // Lọc theo ô Tìm kiếm (Tìm theo Tên hoặc Số điện thoại)
                        if ($current_search !== '') {
                            $search_filter = mysqli_real_escape_string($conn, $current_search);
                            $sql .= " AND (fullname LIKE '%$search_filter%' OR phone LIKE '%$search_filter%')";
                        }

                        // Sắp xếp: Ưu tiên chưa đọc lên đầu, sau đó theo ID mới nhất
                        $sql .= " ORDER BY status ASC, id DESC"; 

                        $res = mysqli_query($conn, $sql);
                        $stt = 1;
                        if($res == TRUE && mysqli_num_rows($res) > 0) {
                            while($row = mysqli_fetch_assoc($res)) {
                                $id = $row['id'];
                                $fullname = $row['fullname'];
                                $phone = $row['phone'];
                                $email = $row['email'];
                                $message = $row['message'];
                                $status = $row['status'];

                                // Cắt ngắn tin nhắn nếu quá dài
                                $short_msg = strlen($message) > 60 ? substr($message, 0, 60) . "..." : $message;

                                // Style làm nổi bật dòng chưa đọc
                                $row_style = ($status == 0) ? "font-weight: 600; background-color: #f8fafc;" : "";
                                ?>
                                <tr style="<?php echo $row_style; ?>">
                                    <td>#<?php echo $stt++ ?></td>
                                    <td><?php echo $id; ?></td>
                                    <td>
                                        <span class="cs-customer-name"><?php echo htmlspecialchars($fullname); ?></span>
                                        <span class="cs-customer-sub"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($phone); ?></span>
                                        <span class="cs-customer-sub"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($email); ?></span>
                                    </td>
                                    <td>
                                        <span style="color: #475569; font-style: italic;">"<?php echo htmlspecialchars($short_msg); ?>"</span>
                                    </td>
                                    <td>
                                        <select class="select-status <?= $status == 0 ? 'new' : 'done' ?>" onchange="
                                            let urlUpdate = 'index.php?page=manage-contact&status=<?= $current_status ?>&action=update_status&id=<?= $id ?>&new_status=' + this.value;
                                            openModal(
                                                'Cập nhật trạng thái?', 
                                                'Xác nhận thay đổi trạng thái liên hệ của khách hàng này?', 
                                                'fa-solid fa-user-check', 
                                                'Đồng ý', 
                                                function() { window.location.href = urlUpdate; }
                                            );
                                        " style="padding: 6px 10px; border-radius: 6px; font-weight: 600;">
                                            <option value="0" <?= $status == 0 ? 'selected' : '' ?>>Mới nhận</option>
                                            <option value="1" <?= $status == 1 ? 'selected' : '' ?>>Đã liên hệ</option>
                                        </select>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="index.php?page=detail_contact&id=<?= $id ?>" class="cs-btn cs-btn-view" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></a>
                                        <a href="actions/delete-contact.php?id=<?= $id ?>" class="cs-btn cs-btn-delete" title="Xóa"
                                            onclick="event.preventDefault(); let urlXoa = this.href; openModal('Xác nhận xóa?', 'Bạn có chắc chắn muốn xóa tin nhắn của khách hàng <?= htmlspecialchars($fullname) ?> không? Dữ liệu sẽ bị xóa vĩnh viễn!', 'fa-solid fa-trash-can', 'Xóa tin nhắn', function() { window.location.href = urlXoa; })">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 30px; color: #64748b;'>Không có tin nhắn liên hệ nào.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>