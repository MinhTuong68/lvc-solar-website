<?php
    session_start();
    include('../../config/constants.php'); 
    include('../../classes/news.php');
    include_once('../admin/auth.php');

    if (isset($_POST['btn_edit_news'])) {
        $news_obj = new News($conn);

        $id = (int)$_POST['id'];
        $title = trim($_POST['title']);
        $slug = trim($_POST['slug']);
        $summary = trim($_POST['summary']);
        $status = (int)$_POST['status'];
        $content = $_POST['content'];
        $old_image = $_POST['old_image'];

        $image = ""; // Mặc định là rỗng (Giữ nguyên ảnh cũ)

        // Nếu có chọn file ảnh mới
        if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
            $image_name = $_FILES['image']['name'];
            $tmp_name = $_FILES['image']['tmp_name'];
            
            // Đổi tên file để tránh trùng
            $ext = pathinfo($image_name, PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array(strtolower($ext), $allowed)) {
                $_SESSION['toast_message'] = "Chỉ cho phép ảnh jpg, png, webp!";
                $_SESSION['toast_type'] = "error";
                header("Location: ../index.php?page=edit-news&id=" . $id);
                exit();
            }
            $image = "news_" . time() . "." . $ext;
            $upload_path = "../../uploads/news/images/" . $image;

            // Upload ảnh mới
            if (move_uploaded_file($tmp_name, $upload_path)) {
                // Xóa ảnh cũ đi cho nhẹ server
                $old_path = "../../uploads/news/images/" . $old_image;
                if (!empty($old_image) && file_exists($old_path) && $old_image != 'default-news.png') {
                    unlink($old_path);
                }
            } else {
                $image = ""; // Lỗi upload thì bỏ qua
            }
        }

        // Gọi hàm Update
        $result = $news_obj->updateNews($id, $title, $slug, $summary, $image, $status, $content);

        if ($result) {
            $_SESSION['toast_message'] = "Cập nhật bài viết thành công!";
            $_SESSION['toast_type'] = "success";
        } else {
            $_SESSION['toast_message'] = "Lỗi: Không thể cập nhật bài viết!";
            $_SESSION['toast_type'] = "error";
        }

        header("Location: ../index.php?page=manage-news");
        exit;
    }
?>