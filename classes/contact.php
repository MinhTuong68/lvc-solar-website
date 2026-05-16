<?php
    // if (!defined('IS_SECURE')) { die('Bạn không có quyền truy cập file này!'); }
    class Contact {
        private $conn;
        public function __construct($db_connection) {
            $this->conn = $db_connection;
        }

        // Hàm lưu tin nhắn khách hàng gửi vào DB
        public function addContact($name, $phone, $email, $subject, $message) {
            $name = mysqli_real_escape_string($this->conn, $name);
            $phone = mysqli_real_escape_string($this->conn, $phone);
            $email = mysqli_real_escape_string($this->conn, $email);
            $subject = mysqli_real_escape_string($this->conn, $subject);
            $message = mysqli_real_escape_string($this->conn, $message);

            $sql = "INSERT INTO tbl_contacts (fullname, phone, email, subject, message) 
                    VALUES ('$name', '$phone', '$email', '$subject', '$message')";
            return mysqli_query($this->conn, $sql);
        }

        // Dành cho Admin: Lấy danh sách liên hệ
        public function getAllContacts() {
            $sql = "SELECT * FROM tbl_contacts ORDER BY id DESC";
            $res = mysqli_query($this->conn, $sql);
            $list = [];
            while($row = mysqli_fetch_assoc($res)) { $list[] = $row; }
            return $list;
        }

        // Hàm xóa tin nhắn liên hệ
        public function deleteContact($id) {
            $id = (int)$id;
            $sql = "DELETE FROM tbl_contacts WHERE id = $id";
            return mysqli_query($this->conn, $sql);
        }
    }
?>