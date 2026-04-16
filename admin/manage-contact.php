<div class="wrapper">
    <div class="cs-page-wrapper">
        <div class="cs-header">
            <h1>Quản lý tin nhắn liên hệ</h1>
            <span style="color: #64748b; font-size: 14px;">Nội dung / Liên hệ</span>
        </div>

        <div class="cs-table-container">
            <table class="cs-table">
                <thead>
                    <tr>
                        <th style="width: 5%">ID</th>
                        <th style="width: 25%">Khách hàng</th>
                        <th style="width: 38%">Nội dung tin nhắn</th>
                        <th style="width: 12%">Trạng thái</th>
                        <th style="width: 20%; text-align: right;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        // Truy vấn dữ liệu từ bảng contacts
                        $sql = "SELECT * FROM contacts ORDER BY is_read ASC, id DESC"; // Ưu tiên xếp chưa đọc lên đầu
                        $res = mysqli_query($conn, $sql);

                        if($res == TRUE && mysqli_num_rows($res) > 0) {
                            while($row = mysqli_fetch_assoc($res)) {
                                $id = $row['id'];
                                $fullname = $row['fullname'];
                                $phone = $row['phone'];
                                $email = $row['email'];
                                $message = $row['message'];
                                $is_read = $row['is_read'];

                                // Cắt ngắn tin nhắn nếu quá dài
                                $short_msg = strlen($message) > 60 ? substr($message, 0, 60) . "..." : $message;

                                // Xử lý UI Trạng thái Đọc
                                if($is_read == 0) {
                                    $status_badge = "<span class='cs-badge badge-unread'>Chưa đọc</span>";
                                    $row_style = "font-weight: 600; background-color: #f8fafc;"; // Tô đậm dòng chưa đọc
                                } else {
                                    $status_badge = "<span class='cs-badge badge-read'>Đã đọc</span>";
                                    $row_style = "";
                                }
                                ?>
                                <tr style="<?php echo $row_style; ?>">
                                    <td>#<?php echo $id; ?></td>
                                    <td>
                                        <span class="cs-customer-name"><?php echo htmlspecialchars($fullname); ?></span>
                                        <span class="cs-customer-sub"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($phone); ?></span>
                                        <span class="cs-customer-sub"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($email); ?></span>
                                    </td>
                                    <td>
                                        <span style="color: #475569; font-style: italic;">"<?php echo htmlspecialchars($short_msg); ?>"</span>
                                    </td>
                                    <td><?php echo $status_badge; ?></td>
                                    <td style="text-align: right;">
                                        <a href="#" class="cs-btn cs-btn-view" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></a>
                                        <a href="#" class="cs-btn cs-btn-delete" title="Xóa"><i class="fa-solid fa-trash-can"></i></a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; padding: 30px; color: #64748b;'>Không có tin nhắn liên hệ nào.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>