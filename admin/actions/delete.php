<?php
    // 1. Nhúng file kết nối DB và toàn bộ các Class
    include('../../config/admin/constants.php'); 
    include('../../classes/brands.php'); 
    include('../../classes/categories.php'); 
    include('../../classes/products.php'); 

    // 2. Kiểm tra xem có nhận đủ type và id không
    if (!isset($_GET['id']) || !isset($_GET['type'])) {
        header("Location: ../index.php");
        exit;
    }

    $id = (int)$_GET['id'];
    $type = $_GET['type'];
    
    $deleteResult = false;
    $redirect_page = 'manage-dashboard'; // Mặc định nếu lỗi thì văng về trang chủ

    // 3. Rẽ nhánh xử lý theo chữ 'type' nhận được
    switch ($type) {
        case 'brand':
            $brandManager = new Brand($conn);
            $deleteResult = $brandManager->deleteBrand($id);
            $redirect_page = 'manage-brands'; // Xóa xong thì về trang Thương hiệu
            break;

        case 'category':
            $categoryManager = new Category($conn);
            $deleteResult = $categoryManager->deleteCategory($id); // Nhớ đảm bảo trong class Category có hàm deleteCategory nhé
            $redirect_page = 'manage-category'; // Xóa xong thì về trang Danh mục
            break;

        case 'product':
            $productManager = new Product($conn);
            $deleteResult = $productManager->deleteProduct($id);
            $redirect_page = 'manage-products'; // Xóa xong thì về trang Sản phẩm
            break;

        default:
            die("Loại dữ liệu không hợp lệ!");
    }

    // 4. In thông báo và chuyển hướng quay lại đúng trang
    if ($deleteResult) {
        $_SESSION['toast_message'] = "Xóa dữ liệu thành công!";
        $_SESSION['toast_type'] = 'success';  
        echo "<script>
                window.location.href = '../index.php?page=' + '$redirect_page';
              </script>";
    } 
    else {
        $_SESSION['toast_message'] = "Xóa thất bại, vui lòng thử lại!";
        $_SESSION['toast_type'] = 'error';  
        echo "<script>
                window.location.href = '../index.php?page=' + '$redirect_page';
              </script>";
    }
?>