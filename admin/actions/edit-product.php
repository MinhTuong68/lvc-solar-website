<?php
session_start();
require_once("../../config/admin/constants.php");
require_once("../../classes/products.php");

if (isset($_POST['btn_edit_product'])) {
    $product_obj = new Product($conn);

    // 1. Nhận dữ liệu cơ bản
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        $_SESSION['toast_message'] = "Lỗi: Không tìm thấy ID sản phẩm!";
        $_SESSION['toast_type'] = "error";
        header("Location: ../index.php?page=manage-products");
        exit();
    }
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if ($name === '' || $slug === '') {
        $_SESSION['toast_message'] = "Tên sản phẩm hoặc slug không được để trống!";
        $_SESSION['toast_type'] = "error";
        header("Location: ../index.php?page=edit-product&id=" . $id);
        exit();
    }
    
    // Nếu select box không chọn gì, gán mặc định rỗng
    $category_id = $_POST['category_id'] ?? '';
    $brand_id = $_POST['brand_id'] ?? '';
    $price = $_POST['price'] ?? 0;
    $old_price = $_POST['old_price'] ?? '';
    $stock = $_POST['stock'] ?? 0;
    $status = $_POST['status'] ?? 1;
    
    $short_description = trim($_POST['short_description'] ?? '');
    $power_capacity = trim($_POST['power_capacity'] ?? '');
    $warranty = trim($_POST['warranty'] ?? '');

    $image_name = $_POST['old_image'] ?? "";;
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        $image = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        
        $ext = pathinfo($image, PATHINFO_EXTENSION);
        $image_name = "image_" . time() . "_" . substr(md5(rand()), 0, 8) . "." . $ext;
        $upload_path = "../../uploads/products/images/" . $image_name;

        // Truy vấn tìm tên ảnh cũ để xóa khỏi ổ cứng (Tối ưu dung lượng)
        $old_sp = mysqli_query($conn, "SELECT image FROM tbl_products WHERE id = $id");
        if ($old_sp) {
            if ($row = mysqli_fetch_assoc($old_sp)) {
                $old_path = "../../uploads/products/images/" . $row['image'];
                if (file_exists($old_path) && !is_dir($old_path) && $row['image'] != "") {
                    unlink($old_path); // Xóa file
                }
            }
        }
        
        // Di chuyển ảnh mới vào thư mục
        move_uploaded_file($image_tmp, $upload_path);
    }

    $update_result = $product_obj->updateProduct($id, $category_id, $brand_id, $name, $slug, $image_name, $power_capacity, $price, $old_price, $warranty, $stock, $short_description, $status);

    if ($update_result) {
        $_SESSION['toast_message'] = "Đã cập nhật thông tin sản phẩm thành công!";
        $_SESSION['toast_type'] = "success";
        header("Location: ../index.php?page=manage-products");
        exit();
    } else {
        $_SESSION['toast_message'] = "Lỗi Database khi cập nhật!";
        $_SESSION['toast_type'] = "error";
        header("Location: ../index.php?page=edit-product&id=" . $id);
        exit();
    }
} else {
    // Nếu ai đó cố tình truy cập file này bằng đường link
    header("Location: ../index.php?page=manage-products");
    exit();
}
?>