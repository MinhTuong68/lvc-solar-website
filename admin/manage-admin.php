<?php
// 1. Chốt chặn bảo mật
require_once 'auth.php';
requireRole('admin'); // Chỉ admin mới có quyền quản lý tài khoản

// 2. Xử lý logic Backend (PHP)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- THÊM ADMIN/STAFF ---
    if ($action == 'add') {
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $role = $_POST['role'];

        $check_email = $conn->query("SELECT id FROM tbl_admins WHERE email = '$email'");
        if ($check_email->num_rows > 0) {
            $_SESSION['toast_message'] = "Lỗi: Email này đã tồn tại trong hệ thống!";
            $_SESSION['toast_type'] = 'error';
            echo "<script>window.location.href='index.php?page=manage-admin';</script>";
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO tbl_admins (fullname, email, password, role, status) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("ssss", $fullname, $email, $password, $role);
        if ($stmt->execute()) {
            $_SESSION['toast_message'] = "Đã thêm tài khoản thành công!";
            $_SESSION['toast_type'] = 'success';
        }
    }

    // --- CẬP NHẬT THÔNG TIN (Tên & Quyền) ---
    if ($action == 'update') {
        $id = (int)$_POST['id'];
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $role = $_POST['role'];

        $stmt = $conn->prepare("UPDATE tbl_admins SET fullname = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $fullname, $role, $id);
        if ($stmt->execute()) {
            $_SESSION['toast_message'] = "Đã cập nhật thông tin!";
            $_SESSION['toast_type'] = 'success';
        }
    }

    if ($action == 'change_pass') {
        $id = (int)$_POST['id'];
        $old_pass = $_POST['old_password']; // Nhận pass cũ
        $new_pass = $_POST['new_password']; // Nhận pass mới

        $stmt_check = $conn->prepare("SELECT password FROM tbl_admins WHERE id = ?");
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        
        if ($res_check->num_rows > 0) {
            $row = $res_check->fetch_assoc();
            if (password_verify($old_pass, $row['password'])) {
                $hashed_new_pass = password_hash($new_pass, PASSWORD_BCRYPT);
                $stmt_update = $conn->prepare("UPDATE tbl_admins SET password = ? WHERE id = ?");
                $stmt_update->bind_param("si", $hashed_new_pass, $id);
                if ($stmt_update->execute()) {
                    $_SESSION['toast_message'] = "Đã đổi mật khẩu thành công!";
                    $_SESSION['toast_type'] = 'success';
                }
            } else {
                $_SESSION['toast_message'] = "Lỗi: Mật khẩu cũ không chính xác!";
                $_SESSION['toast_type'] = 'error';
            }
        }
    }

    // --- XÓA TÀI KHOẢN ---
    if ($action == 'delete') {
        $id = (int)$_POST['id'];
        if ($id != $_SESSION['admin_id']) { // Không cho tự xóa chính mình
            $stmt = $conn->prepare("DELETE FROM tbl_admins WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $_SESSION['toast_message'] = "Đã xóa tài khoản!";
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = "Không thể tự xóa bản thân!";
            $_SESSION['toast_type'] = 'error';
        }
    }

    echo "<script>window.location.href='index.php?page=manage-admin';</script>";
    exit;
}

// 3. Lấy danh sách tài khoản
$res = $conn->query("SELECT * FROM tbl_admins ORDER BY id DESC");
?>

<style>
    /* CSS cho Modal */
    .modal-admin {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0; top: 0; width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    .modal-content-admin {
        background: white; margin: 10% auto; padding: 20px;
        width: 400px; border-radius: 10px; position: relative;
    }
    .close-btn { position: absolute; right: 15px; top: 10px; cursor: pointer; font-size: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
    
    /* Tận dụng style cũ của anh */
    .tbl-full { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
    .tbl-full th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    .tbl-full th { background: var(--navy); color: white; }
    .btn-action { padding: 5px 10px; border-radius: 4px; font-size: 12px; text-decoration: none; color: white; border: none; cursor: pointer; }
    .bg-blue { background: #3498db; }
    .bg-orange { background: #f39c12; }
    .bg-red { background: #e74c3c; }
</style>

<div class="wrapper">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fa-solid fa-user-shield"></i> Quản lý nội bộ</h2>
        <button class="btn-primary" onclick="openAdminModal('modalAdd')">+ Thêm nhân sự</button>
    </div>

    <table class="tbl-full">
        <tr>
            <th>STT</th>
            <th>Họ tên</th>
            <th>Email (Login)</th>
            <th>Vai trò</th>
            <th>Hành động</th>
        </tr>
        <?php 
        $sn = 1;
        while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $sn++ ?></td>
            <td><strong><?= htmlspecialchars($row['fullname']) ?></strong></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td>
                <span class="badge" style="background: <?= $row['role']=='admin' ? '#dcfce7':'#f1f5f9' ?>; color: <?= $row['role']=='admin' ? '#166534':'#475569' ?>; padding: 4px 8px; border-radius: 4px; font-weight: bold;">
                    <?= strtoupper($row['role']) ?>
                </span>
            </td>
            <td>
                <button class="btn-action bg-blue" onclick="openEditModal(<?= $row['id'] ?>, '<?= $row['fullname'] ?>', '<?= $row['role'] ?>')">Sửa</button>
                <button class="btn-action bg-orange" onclick="openPassModal(<?= $row['id'] ?>)">Pass</button>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa tài khoản này?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn-action bg-red">Xóa</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<div id="modalAdd" class="modal-admin">
    <div class="modal-content-admin">
        <span class="close-btn" onclick="closeAdminModal('modalAdd')">&times;</span>
        <h3>Thêm thành viên mới</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Họ tên</label>
                <input type="text" name="fullname" required>
            </div>
            <div class="form-group">
                <label>Email đăng nhập</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Quyền hạn</label>
                <select name="role">
                    <option value="staff">Nhân viên (Staff)</option>
                    <option value="admin">Quản trị viên (Admin)</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="width:100%">Xác nhận thêm</button>
        </form>
    </div>
</div>

<div id="modalEdit" class="modal-admin">
    <div class="modal-content-admin">
        <span class="close-btn" onclick="closeAdminModal('modalEdit')">&times;</span>
        <h3>Cập nhật thông tin</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label>Họ tên</label>
                <input type="text" name="fullname" id="edit_fullname" required>
            </div>
            <div class="form-group">
                <label>Quyền hạn</label>
                <select name="role" id="edit_role">
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="width:100%">Lưu thay đổi</button>
        </form>
    </div>
</div>

<div id="modalPass" class="modal-admin">
    <div class="modal-content-admin">
        <span class="close-btn" onclick="closeAdminModal('modalPass')">&times;</span>
        <h3>Đổi mật khẩu</h3>
        <form method="POST">
            <input type="hidden" name="action" value="change_pass">
            <input type="hidden" name="id" id="pass_id">
            <div class="form-group">
                <label>Mật khẩu CŨ</label>
                <input type="password" name="old_password" required placeholder="Nhập mật khẩu hiện tại...">
            </div>
            <div class="form-group">
                <label>Mật khẩu MỚI</label>
                <input type="password" name="new_password" required minlength="6" placeholder="Nhập mật khẩu mới...">
            </div>
            <button type="submit" class="btn-primary" style="width:100%">Cập nhật mật khẩu</button>
        </form>
    </div>
</div>

<script>
    function openAdminModal(id) { document.getElementById(id).style.display = "block"; }
    function closeAdminModal(id) { document.getElementById(id).style.display = "none"; }

    function openEditModal(id, name, role) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_fullname').value = name;
        document.getElementById('edit_role').value = role;
        openAdminModal('modalEdit');
    }

    function openPassModal(id) {
        document.getElementById('pass_id').value = id;
        openAdminModal('modalPass');
    }

    // Đóng khi click ngoài vùng modal
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-admin') || event.target.className === 'modal-admin') {
            event.target.style.display = "none";
        }
    }
</script>