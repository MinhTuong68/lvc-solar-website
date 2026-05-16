<?php
    include('../../config/constants.php');
    include('../../classes/projects.php');
    include_once('../admin/auth.php');

    if (isset($_POST['btn_edit_project'])) {
        $project_obj = new Project($conn);

        // 1. LẤY DỮ LIỆU CƠ BẢN TỪ FORM
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $slug = trim($_POST['project_slug']);
        $client = trim($_POST['client']);
        $completion_date = $_POST['completion_date'];
        $location = trim($_POST['location']);
        $capacity = trim($_POST['capacity']);
        $description = trim($_POST['description']);
        $is_featured = (int)$_POST['is_featured'];
        $status = (int)$_POST['status'];
        $content = $_POST['content'];
        $old_image = $_POST['old_image'];

        $image = ""; // Mặc định không đổi ảnh đại diện

        // 2. XỬ LÝ UPLOAD ẢNH ĐẠI DIỆN MỚI (NẾU CÓ)
        if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array(strtolower($ext), $allowed)) {
                $_SESSION['toast_message'] = "Chỉ cho phép ảnh jpg, png, webp!";
                $_SESSION['toast_type'] = "error";
                header("Location: ../index.php?page=edit-project&id=" . $id);
                exit();
            }
            $image = "pj_" . time() . "." . $ext;
            $upload_path = "../../uploads/projects/images/" . $image;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Xóa ảnh đại diện cũ
                $old_path = "../../uploads/projects/images/" . $old_image;
                if (!empty($old_image) && file_exists($old_path) && $old_image != 'default.jpg') {
                    unlink($old_path);
                }
            } else {
                $image = "";
            }
        }

        // 3. CẬP NHẬT THÔNG TIN CHÍNH VÀO DATABASE
        $result = $project_obj->updateProject($id, $name, $slug, $client, $completion_date, $location, $capacity, $description, $image, $is_featured, $status, $content);

        // 4. XỬ LÝ THƯ VIỆN ẢNH PHỤ (GALLERY) KHI UPDATE THÀNH CÔNG
        if ($result) {
            
            // 4.1. XÓA ẢNH PHỤ (Những ảnh người dùng đã bấm nút thùng rác)
            if (isset($_POST['delete_gallery']) && is_array($_POST['delete_gallery'])) {
                foreach ($_POST['delete_gallery'] as $del_id) {
                    $del_id = (int)$del_id;
                    // Lấy tên file ảnh để xóa vật lý cho nhẹ Server
                    $sql_get_img = "SELECT image FROM tbl_project_gallery WHERE id = $del_id";
                    $res_img = mysqli_query($conn, $sql_get_img);
                    if ($row = mysqli_fetch_assoc($res_img)) {
                        $img_path = "../../uploads/projects/gallery/" . $row['image'];
                        if (file_exists($img_path)) {
                            unlink($img_path);
                        }
                    }
                    // Xóa data trong SQL
                    mysqli_query($conn, "DELETE FROM tbl_project_gallery WHERE id = $del_id");
                }
            }

            // 4.2. CẬP NHẬT THỨ TỰ ẢNH (Do người dùng kéo thả)
            if (isset($_POST['gallery_sort_order']) && is_array($_POST['gallery_sort_order'])) {
                foreach ($_POST['gallery_sort_order'] as $index => $gal_id) {
                    $gal_id = (int)$gal_id;
                    $sort = $index + 1;
                    mysqli_query($conn, "UPDATE tbl_project_gallery SET sort_order = $sort WHERE id = $gal_id");
                }
            }

            // 4.3. UPLOAD ẢNH MỚI CHỌN VÀO THƯ VIỆN
            if (isset($_FILES['gallery']['name'][0]) && $_FILES['gallery']['name'][0] != "") {
                $total_files = count($_FILES['gallery']['name']);
                
                // Tìm vị trí sắp xếp tiếp theo (Tránh bị đè lên ảnh cũ)
                $res_max = mysqli_query($conn, "SELECT MAX(sort_order) AS max_sort FROM tbl_project_gallery WHERE project_id = $id");
                $max_sort = mysqli_fetch_assoc($res_max)['max_sort'];
                $current_sort = $max_sort ? $max_sort + 1 : 1;

                // Lặp qua từng file để Upload
                for ($i = 0; $i < $total_files; $i++) {
                    $file_name = $_FILES['gallery']['name'][$i];
                    $tmp_name = $_FILES['gallery']['tmp_name'][$i];
                    
                    if ($file_name != "") {
                        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                        if (!in_array(strtolower($ext), $allowed)) {
                            $_SESSION['toast_message'] = "Chỉ cho phép ảnh jpg, png, webp!";
                            $_SESSION['toast_type'] = "error";
                            header("Location: ../index.php?page=edit-project&id=" . $id);
                            exit();
                        }
                        // Đổi tên tránh trùng lặp
                        $new_name = "gal_pj_" . $id . "_" . time() . "_" . $i . "." . $ext;
                        $upload_path = "../../uploads/projects/gallery/" . $new_name;

                        // Chuyển file và ghi vào CSDL
                        if (move_uploaded_file($tmp_name, $upload_path)) {
                            $sql_insert_gal = "INSERT INTO tbl_project_gallery (project_id, image, sort_order) VALUES ($id, '$new_name', $current_sort)";
                            mysqli_query($conn, $sql_insert_gal);
                            $current_sort++;
                        }
                    }
                }
            }

            // Ghi thông báo thành công
            $_SESSION['toast_message'] = "Cập nhật dự án và thư viện ảnh thành công!";
            $_SESSION['toast_type'] = "success";
        } else {
            // Ghi thông báo lỗi nếu không update được
            $_SESSION['toast_message'] = "Lỗi: Không thể cập nhật dự án!";
            $_SESSION['toast_type'] = "error";
        }

        // Quay về trang quản lý dự án
        header("Location: ../index.php?page=manage-projects");
        exit;
    }
?>