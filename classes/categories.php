<?php
class Category {
    private $conn;

    // Hàm khởi tạo, nhận kết nối Database truyền vào
    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // 1. HÀM THÊM DANH MỤC MỚI
    public function addCategory($name, $slug, $status) {
        // Chống SQL Injection
        $name = mysqli_real_escape_string($this->conn, $name);
        $slug = mysqli_real_escape_string($this->conn, $slug);
        $status = (int)$status;

        // Kiểm tra xem Slug (đường dẫn) đã tồn tại chưa để tránh trùng lặp
        $check_sql = "SELECT id FROM tbl_categories WHERE slug = '$slug'";
        $check_res = mysqli_query($this->conn, $check_sql);
        if (mysqli_num_rows($check_res) > 0) {
            return "exists"; // Báo lỗi trùng
        }

        // Câu lệnh SQL thêm vào Database
        $sql = "INSERT INTO tbl_categories (name, slug, status) VALUES ('$name', '$slug', $status)";

        if (mysqli_query($this->conn, $sql)) {
            return "success";
            // return mysqli_insert_id($this->conn); 
        } else {
            die("SQL ERROR: " . mysqli_error($this->conn));
        }
    }

    // 2. HÀM LẤY DANH SÁCH DANH MỤC ĐỂ IN RA BẢNG
    public function getAllCategories() {
        // Lấy tất cả, sắp xếp ID giảm dần (mới thêm lên đầu)
        $sql = "SELECT * FROM tbl_categories ORDER BY id DESC";
        $result = mysqli_query($this->conn, $sql);
        
        $categories = [];
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $categories[] = $row;
            }
        }
        return $categories;
    }

    public function getTotalCategories() {
        $sql = "SELECT COUNT(id) as total FROM tbl_categories";
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }

    public function getCategoriesPaginated($limit, $offset) {
        $sql = "SELECT * FROM tbl_categories ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $result = mysqli_query($this->conn, $sql);
        
        $categories = [];
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $categories[] = $row;
            }
        }
        return $categories;
    }

    public function deleteCategory($id) {
        $id = (int)$id;

        // Câu lệnh SQL xóa danh mục bằng Prepared Statement để bảo mật
        $sql = "DELETE FROM tbl_categories WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            return $result; // Trả về true nếu xóa thành công
        }
        
        return false;
    }
    // Hàm lấy chi tiết một danh mục theo ID
    public function getCategoryByID($id) {
        $id = (int)$id;
        $sql = "SELECT * FROM tbl_categories WHERE id = $id";
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return null;
        }
    }

    // Hàm cập nhật danh mục
    public function updateCategory($id, $name, $slug, $status) {
        $id = (int)$id;
        $name = mysqli_real_escape_string($this->conn, $name);
        $slug = mysqli_real_escape_string($this->conn, $slug);
        $status = (int)$status;

        // Kiểm tra xem Slug mới có bị trùng với danh mục khác không
        $check_sql = "SELECT id FROM tbl_categories WHERE slug = '$slug' AND id != $id";
        $check_res = mysqli_query($this->conn, $check_sql);
        
        if (mysqli_num_rows($check_res) > 0) {
            return "exists"; // Trả về lỗi nếu trùng slug
        } else {
            // Nếu không trùng thì tiến hành Update
            $sql = "UPDATE tbl_categories SET 
                    name = '$name', 
                    slug = '$slug', 
                    status = $status 
                    WHERE id = $id";

            if (mysqli_query($this->conn, $sql)) {
                return "success";
            } else {
                return "error";
            }
        }
    }
}
?>