<?php
    session_start();
    include("../../config/publics/constants.php");
    include("../../classes/services.php");
    $service_obj = new Service($conn);

    // 2. Xử lý khi khách hàng bấm nút GỬI YÊU CẦU
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_submit_quote'])) {
        // Lấy dữ liệu từ form
        $name = $_POST['customer_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $service_type_id = $_POST['service_type_id'] ?? 0;
        $bill = $_POST['electricity_bill'] ?? '';
        $issue = $_POST['issue_description'] ?? '';
        
        // Những cột DB có mà form không có (Email, Dung lượng, Ngày hẹn) thì truyền chuỗi rỗng
        $email = ''; 
        $capacity = '';
        $booking_date = '';

        // Gọi hàm AddService để lưu vào CSDL
        $result = $service_obj->addService($name, $phone, $email, $address, $service_type_id, $bill, $capacity, $issue, $booking_date);

        // Thông báo kết quả
        if ($result) {
            $_SESSION['toast_message'] = "Gửi yêu cầu thành công! LVC Solar sẽ liên hệ ngay.";
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = "Lỗi hệ thống, vui lòng thử lại sau.";
            $_SESSION['toast_type'] = 'error';
        }
        
        // Tránh tình trạng khách F5 bị gửi lại form 2 lần
        header("Location: ../index.php?page=home");
        exit();
    }

    // 3. Lấy danh sách loại dịch vụ để tự động in ra form
    $allTypes = $service_obj->getAllServiceTypes();
?>