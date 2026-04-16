<?php
class Brand {
    private $conn;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // 1. Hàm thêm Thương hiệu (có xử lý lưu tên file logo)
    public function addBrand($name, $slug, $logo, $status) {
        $name = mysqli_real_escape_string($this->conn, $name);
        $slug = mysqli_real_escape_string($this->conn, $slug);
        $status = (int)$status;

        // Kiểm tra xem Slug đã tồn tại chưa
        $check_sql = "SELECT id FROM tbl_brands WHERE slug = '$slug'";
        $check_res = mysqli_query($this->conn, $check_sql);
        if (mysqli_num_rows($check_res) > 0) {
            return "exists"; 
        }

        // Câu lệnh SQL thêm vào Database
        $sql = "INSERT INTO tbl_brands (name, slug, logo, status) VALUES ('$name', '$slug', '$logo', $status)";

        if (mysqli_query($this->conn, $sql)) {
            return "success"; 
        } else {
            die("SQL ERROR: " . mysqli_error($this->conn));
        }
    }

    // 2. Hàm đếm tổng số thương hiệu (Dùng cho phân trang)
    public function getTotalBrands() {
        $sql = "SELECT COUNT(id) as total FROM tbl_brands";
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }

    // 3. Hàm lấy danh sách thương hiệu theo phân trang
    public function getBrandsPaginated($limit, $offset) {
        $sql = "SELECT * FROM tbl_brands ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $result = mysqli_query($this->conn, $sql);
        
        $brands = [];
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $brands[] = $row;
            }
        }
        return $brands;
    }

    // Hàm lấy TẤT CẢ thương hiệu (Dùng cho các ô Dropdown/Select)
    public function getAllBrands() {
        $sql = "SELECT * FROM tbl_brands ORDER BY name ASC";
        $result = mysqli_query($this->conn, $sql);
        
        $brands = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $brands[] = $row;
            }
        }
        return $brands;
    }

    // ==================================================
    // 5. Hàm XÓA THƯƠNG HIỆU (Và dọn dẹp file Logo)
    // ==================================================
    public function deleteBrand($id) {
        $id = (int)$id;

        // BƯỚC 1: LẤY TÊN FILE LOGO ĐỂ XÓA KHỎI Ổ CỨNG
        $query_logo = "SELECT logo FROM tbl_brands WHERE id = ?";
        $stmt_logo = mysqli_prepare($this->conn, $query_logo);
        
        if ($stmt_logo) {
            mysqli_stmt_bind_param($stmt_logo, "i", $id);
            mysqli_stmt_execute($stmt_logo);
            $result_logo = mysqli_stmt_get_result($stmt_logo);
            
            if ($row = mysqli_fetch_assoc($result_logo)) {
                $logo_name = $row['logo'];
                
                // Chỉ xóa file nếu có tên ảnh VÀ không phải là ảnh mặc định
                if (!empty($logo_name) && $logo_name !== 'default_brand.png') {
                    $logo_path = "../uploads/brands/images/" . $logo_name;
                    // Kiểm tra xem file có thực sự tồn tại trên máy chủ không rồi mới xóa
                    if (file_exists($logo_path)) {
                        unlink($logo_path); 
                    }
                }
            }
            mysqli_stmt_close($stmt_logo);
        }

        // BƯỚC 2: XÓA DỮ LIỆU TRONG BẢNG tbl_brands
        $sql = "DELETE FROM tbl_brands WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result; // Trả về true nếu xóa thành công
        }
        
        return false;
    }
}
?>