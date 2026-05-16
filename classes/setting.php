<?php
class Setting {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function getSettings() {
        $sql = "SELECT * FROM tbl_settings LIMIT 1"; 
        $res = mysqli_query($this->conn, $sql);
        return ($res && mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res) : false;
    }

    // HÀM NÀY PHẢI CÓ ĐỦ 9 THAM SỐ NHƯ DƯỚI ĐÂY
    public function updateSettings($site_name, $site_description, $hotline, $email, $address, $fb, $yt, $zalo, $logo) {
        $sql = "UPDATE tbl_settings SET 
                site_name = ?, site_description = ?, hotline = ?, email = ?, 
                address = ?, facebook_link = ?, youtube_link = ?, zalo_link = ?, logo = ? 
                WHERE id = 1";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        if ($stmt) {
            // "sssssssss" là 9 chữ s cho 9 biến chuỗi
            mysqli_stmt_bind_param($stmt, "sssssssss", $site_name, $site_description, $hotline, $email, $address, $fb, $yt, $zalo, $logo);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }
}
?>