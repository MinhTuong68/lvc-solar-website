<?php
include('../../config/constants.php');
include('../../classes/projects.php');
include('../admin/auth.php'); 
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die(json_encode(['uploaded' => 0, 'error' => ['message' => 'Unauthorized']]));
}

$projectObj = new Project($conn);

if (isset($_POST['btn_add_project'])) {
    // 1. Nhận dữ liệu text từ Form
    $name = $_POST['name'];
    $slug = $_POST['project_slug'] ?? ''; // Lấy đúng tên thẻ name trong HTML
    $client = $_POST['client'] ?? '';
    $completion_date = $_POST['completion_date'] ?? '';
    $location = $_POST['location'] ?? '';
    $capacity = $_POST['capacity'] ?? '';
    $description = $_POST['description'] ?? '';
    $is_featured = $_POST['is_featured'] ?? 0;
    $status = $_POST['status'] ?? 1;
    $content = $_POST['content'] ?? '';

    // 2. Xử lý Upload Ảnh Đại Diện (Thumbnail chính)
    $image_name = '';
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array(strtolower($ext), $allowed)) {
            $_SESSION['toast_message'] = "Chỉ cho phép ảnh jpg, png, webp!";
            $_SESSION['toast_type'] = "error";
            header("Location: ../index.php?page=manage-add-projects");
            exit();
        }
        // Đổi tên ảnh ngẫu nhiên để không bị trùng (VD: project_169999_abc.jpg)
        $image_name = "project_" . time() . "_" . substr(md5(rand()), 0, 8) . "." . $ext; 
        
        $source_path = $_FILES['image']['tmp_name'];
        $destination_path = "../../uploads/projects/images/" . $image_name;

        // Tải ảnh lên
        if (!move_uploaded_file($source_path, $destination_path)) {
            $_SESSION['toast_message'] = "Lỗi: Không thể tải ảnh đại diện lên máy chủ!";
            $_SESSION['toast_type'] = "error";
            header("Location: ../index.php?page=manage-add-projects");
            exit;
        }
    }

    // 3. Đẩy dữ liệu vào CSDL thông qua Class Project
    $result = $projectObj->addProject($name, $slug, $client, $completion_date, $location, $capacity, $description, $image_name, $is_featured, $status, $content);

    // Xử lý các kết quả trả về từ Class
    if ($result === "exists") {
        $_SESSION['toast_message'] = "Lỗi: Đường dẫn (Slug) này đã tồn tại, vui lòng đổi tên dự án khác!";
        $_SESSION['toast_type'] = "error";
        header("Location: ../index.php?page=manage-add-projects");
        exit;
    } elseif ($result > 0) {
        $project_id = $result; // ID của dự án vừa được thêm thành công

        // ==========================================
        // 4. XỬ LÝ UPLOAD NHIỀU ẢNH (THƯ VIỆN GALLERY)
        // ==========================================
        if (isset($_FILES['gallery']['name']) && count($_FILES['gallery']['name']) > 0 && $_FILES['gallery']['name'][0] != "") {
            $total_images = count($_FILES['gallery']['name']);
            
            for ($i = 0; $i < $total_images; $i++) {
                $gal_ext = pathinfo($_FILES['gallery']['name'][$i], PATHINFO_EXTENSION);
                $gal_image_name = "gallery_" . time() . "_" . substr(md5(rand()), 0, 8) . "." . $gal_ext;
                
                $gal_source = $_FILES['gallery']['tmp_name'][$i];
                $gal_dest = "../../uploads/projects/gallery/" . $gal_image_name;

                // Nếu ảnh được tải lên thư mục thành công thì lưu tên nó vào bảng tbl_project_gallery
                if (move_uploaded_file($gal_source, $gal_dest)) {
                    $projectObj->addProjectGallery($project_id, $gal_image_name);
                }
            }
        }

        // 5. Hoàn tất -> Hiện thông báo thành công và chuyển hướng về danh sách
        $_SESSION['toast_message'] = "Thêm dự án thi công thành công!";
        $_SESSION['toast_type'] = "success";
        header("Location: ../index.php?page=manage-projects");
        exit;
    } else {
        $_SESSION['toast_message'] = "Lỗi: Hệ thống gặp trục trặc khi lưu dữ liệu!";
        $_SESSION['toast_type'] = "error";
        header("Location: ../index.php?page=manage-add-projects");
        exit;
    }
} else {
    // Nếu ai đó cố tình truy cập link này trực tiếp mà không bấm nút "Lưu"
    header("Location: ../index.php?page=manage-projects");
    exit;
}
?>