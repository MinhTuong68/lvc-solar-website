<?php
    if (!defined('IS_SECURE')) { die('Bạn không có quyền truy cập file này!'); }
    class Service{
        private $conn;

        public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    public function addservicetype($service_type){
        $service_type = mysqli_real_escape_string($this->conn, $service_type);
        $sql = "INSERT INTO tbl_service_type (service_type) VALUES ('$service_type')";
        return mysqli_query($this->conn, $sql);
    }

    public function getAllServiceTypes() {
        $sql = "SELECT * FROM tbl_service_type";
        $res = mysqli_query($this->conn, $sql);
        $types = [];
        if($res && mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                $types[] = $row;
            }
        }
        return $types;
    }

    public function addService($name, $phone, $email, $address, $service_type_id, $bill, $capacity, $issue, $booking_date){
        $name = mysqli_real_escape_string($this->conn, $name);
        $phone = mysqli_real_escape_string($this->conn, $phone);
        $email = mysqli_real_escape_string($this->conn, $email); // Đã bổ sung Email
        $address = mysqli_real_escape_string($this->conn, $address);
        $service_type = (int)$service_type_id;
        $bill = mysqli_real_escape_string($this->conn, $bill);
        $capacity = mysqli_real_escape_string($this->conn, $capacity);
        $issue = mysqli_real_escape_string($this->conn, $issue);
        
        $date_sql = (!empty($booking_date)) ? "'$booking_date'" : "NULL";

        $sql = "INSERT INTO tbl_services 
                (customer_name, phone, email, address, service_type_id, electricity_bill, system_capacity, issue_description, booking_date) 
                VALUES 
                ('$name', '$phone', '$email', '$address', $service_type_id, '$bill', '$capacity', '$issue', $date_sql)";

        return mysqli_query($this->conn, $sql);
    }

    public function getAllServices($status = 'all', $type = '') {
        $sql = "SELECT s.*, t.service_type 
                FROM tbl_services s
                LEFT JOIN tbl_service_type t ON s.service_type_id = t.id 
                WHERE 1=1";
        
        // Lọc theo Tab Trạng thái
        if ($status !== 'all' && $status !== '') {
            $status = mysqli_real_escape_string($this->conn, $status);
            $sql .= " AND status = '$status'";
        }
        
        // Lọc theo ComboBox Loại dịch vụ (Lọc bằng ID)
        if ($type !== '') {
            $type = (int)$type;
            $sql .= " AND s.service_type_id = $type";
        }

        // Sắp xếp: Đơn 'new' ưu tiên lên đầu, sau đó sắp xếp theo ngày cập nhật mới nhất
        $sql .= " ORDER BY FIELD(status, 'new') DESC, updated_at DESC";
        
        $result = mysqli_query($this->conn, $sql);
        
        $services = [];
        if($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $services[] = $row;
            }
        }
        return $services;
    }

    // =========================================================
    // 3. XÓA DỊCH VỤ
    // =========================================================
    public function deleteService($id) {
        $id = (int)$id;
        $sql = "DELETE FROM tbl_services WHERE id = $id";
        return mysqli_query($this->conn, $sql);
    }
    }
?>