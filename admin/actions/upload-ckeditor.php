<?php
include('../../config/constants.php');
include_once('../admin/auth.php');
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die(json_encode(['uploaded' => 0, 'error' => ['message' => 'Unauthorized']]));
}

// Đường dẫn lưu ảnh (lùi lại 1 cấp từ thư mục actions ra ngoài, rồi vào thư mục uploads/editor)
$upload_dir = '../../uploads/editor/'; 

// Nếu thư mục editor chưa tồn tại thì PHP tự động tạo luôn
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Kiểm tra xem CKEditor có gửi file lên không (CKEditor gửi file qua biến $_FILES['upload'])
if(isset($_FILES['upload']['name'])) {
    $file = $_FILES['upload']['tmp_name'];
    $file_name = $_FILES['upload']['name'];
    
    // Lấy đuôi mở rộng của file (ví dụ: jpg, png)
    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array(strtolower($ext), $allowed)) {
        die(json_encode(['uploaded' => 0, 'error' => ['message' => 'File không hợp lệ']]));
    }
    
    // Đổi tên file để tránh trùng lặp (VD: img_171000_a1b2.jpg)
    $new_name = "img_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
    
    // Đường dẫn tuyệt đối để chuyển file vào
    $destination = $upload_dir . $new_name;

    // Tiến hành lưu file
    if(move_uploaded_file($file, $destination)) {
        
        // Đường dẫn URL để hiển thị trên web
        $url = ROOT_URL .'/uploads/editor/' . $new_name; 

        // CKEditor yêu cầu phải trả về một chuỗi JSON như thế này để nó hiểu là đã xong
        $response = [
            "uploaded" => 1,
            "fileName" => $new_name,
            "url" => $url
        ];
        echo json_encode($response);
    } else {
        // Báo lỗi nếu lưu thất bại
        $response = [
            "uploaded" => 0,
            "error" => ["message" => "Lỗi: Không thể lưu ảnh vào máy chủ."]
        ];
        echo json_encode($response);
    }
}
?>