<?php
require_once '../../config/constants.php'; 
require_once '../../classes/reviews.php'; 

// Khởi tạo Object Review bằng biến $conn có sẵn từ file config.php
$reviewObj = new Review($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Kiểm tra CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['toast_message'] = "Lỗi bảo mật!";
        $_SESSION['toast_type'] = "error";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    $product_id = (int)$_POST['product_id'];
    $customer_name  = e(trim($_POST['customer_name'] ?? ''));
    $customer_email = e(trim($_POST['customer_email'] ?? ''));
    $content        = e(trim($_POST['content'] ?? ''));
    $rating         = max(1, min(5, (int)($_POST['rating'] ?? 5)));

    if ($reviewObj->checkReviewExists($product_id, $customer_email)) {
        $_SESSION['toast_message'] = "Email này đã đánh giá sản phẩm này rồi!";
        $_SESSION['toast_type'] = "error";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit; 
    }
    
    // 2. Xử lý Upload Ảnh
    $uploaded_images = [];
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $totalFiles = min(count($_FILES['images']['name']), 3); // Lấy tối đa 3 ảnh
  
        $upload_dir = '../../uploads/products/reviews/'; 
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        for ($i = 0; $i < $totalFiles; $i++) {
            $tmp_name = $_FILES['images']['tmp_name'][$i];
            $file_ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
            $file_size = $_FILES['images']['size'][$i];
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
            $allowed_mimes = ['image/jpeg','image/png','image/webp'];
            if (in_array($file_ext, $allowed_ext) && in_array($mime, $allowed_mimes) && $file_size < 2097152) {
                $new_name = 'rv_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
                if (move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
                    $uploaded_images[] = $new_name;
                }
            }
        }
    }
    $images_str = implode(',', $uploaded_images);

    // 3. GỌI HÀM TỪ CLASS ĐỂ LƯU VÀO DB
    if ($reviewObj->addReview($product_id, $customer_name, $customer_email, $rating, $content, $images_str)) {
        $_SESSION['toast_message'] = "Đánh giá của bạn đã được ghi nhận!";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_message'] = "Lỗi hệ thống khi lưu đánh giá!";
        $_SESSION['toast_type'] = "error";
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>