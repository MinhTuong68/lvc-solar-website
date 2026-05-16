<?php
class News {
    private $conn;

    // Khởi tạo kết nối DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. Lấy danh sách TẤT CẢ tin tức (Dùng cho trang Admin)
    public function getAllNewsAdmin() {
        $sql = "SELECT * FROM tbl_news ORDER BY id DESC";
        $result = $this->conn->query($sql);
        $news = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $news[] = $row;
            }
        }
        return $news;
    }

    // 2. Lấy danh sách tin tức ĐANG HIỂN THỊ (Dùng cho trang chủ/trang khách hàng)
    public function getActiveNews() {
        $sql = "SELECT id, title, slug, image, summary, created_at, views 
                FROM tbl_news 
                WHERE status = 1 
                ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        $news = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $news[] = $row;
            }
        }
        return $news;
    }

    // 3. Lấy chi tiết bài viết khi khách click vào xem (Tìm theo đường dẫn Slug)
    public function getNewsBySlug($slug) {
        $slug = $this->conn->real_escape_string($slug);
        
        // Tăng lượt xem (views) lên 1 mỗi khi có người đọc
        $updateView = "UPDATE tbl_news SET views = views + 1 WHERE slug = '$slug'";
        $this->conn->query($updateView);

        // Lấy dữ liệu bài viết ra
        $sql = "SELECT * FROM tbl_news WHERE slug = '$slug' AND status = 1 LIMIT 1";
        $result = $this->conn->query($sql);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    // Hàm thêm bài viết mới vào CSDL
    public function addNews($title, $slug, $image_name, $summary, $content, $status, $author) {
        // Chuẩn bị câu lệnh SQL
        $sql = "INSERT INTO tbl_news (title, slug, image, summary, content, status, author) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
                
        // Sử dụng Prepare Statement để bảo mật (Chống SQL Injection)
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            // Liên kết dữ liệu (s = string, i = integer)
            $stmt->bind_param("sssssis", $title, $slug, $image_name, $summary, $content, $status, $author);
            
            // Thực thi và trả về kết quả
            if($stmt->execute()){
                return true;
            }
        }
        return false;
    }

    // Thêm hàm này vào file classes/news.php
    // Hàm lấy bài viết nổi bật (Chuẩn MySQLi)
    public function getPopularNews($limit = 5) {
        // Dùng đúng tên bảng tbl_news và dấu ? cho tham số
        $sql = "SELECT * FROM tbl_news WHERE status = 1 ORDER BY views DESC LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $news = [];
        
        if ($stmt) {
            // 'i' đại diện cho kiểu số nguyên (integer)
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            
            // Lấy kết quả trả về
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $news[] = $row;
                }
            }
        }
        
        return $news;
    }

    // Viết thêm hàm này vào class News
    public function deleteNews($id) {
        $id = (int)$id;

        // 1. Tìm tên ảnh đại diện để xóa khỏi thư mục (Giúp nhẹ Host)
        $sql_img = "SELECT image FROM tbl_news WHERE id = $id";
        $result_img = $this->conn->query($sql_img);
        
        if ($result_img && $result_img->num_rows > 0) {
            $row = $result_img->fetch_assoc();
            $img_name = $row['image'];
            
            // Không xóa ảnh mặc định
            if ($img_name != "" && $img_name != "default_news.jpg" && $img_name != "default-news.png") {
                $img_path = "../../uploads/news/images/" . $img_name;
                if (file_exists($img_path)) {
                    unlink($img_path); // Lệnh xóa file vật lý
                }
            }
        }

        // 2. Xóa dữ liệu trong Database
        $sql = "DELETE FROM tbl_news WHERE id = $id";
        return $this->conn->query($sql);
    }

    // 1. Lấy chi tiết 1 bài viết theo ID để đổ ra form
    public function getNewsByID($id) {
        $id = (int)$id;
        $sql = "SELECT * FROM tbl_news WHERE id = $id";
        $result = $this->conn->query($sql);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    // 2. Cập nhật bài viết
    public function updateNews($id, $title, $slug, $summary, $image, $status, $content) {
        $id = (int)$id;
        $title = mysqli_real_escape_string($this->conn, $title);
        $slug = mysqli_real_escape_string($this->conn, $slug);
        $summary = mysqli_real_escape_string($this->conn, $summary);
        $content = mysqli_real_escape_string($this->conn, $content);
        $status = (int)$status;

        // Nếu có up ảnh mới thì cập nhật luôn cột image, không thì giữ nguyên
        if ($image != "") {
            $image = mysqli_real_escape_string($this->conn, $image);
            $sql = "UPDATE tbl_news SET title='$title', slug='$slug', summary='$summary', image='$image', status=$status, content='$content' WHERE id=$id";
        } else {
            $sql = "UPDATE tbl_news SET title='$title', slug='$slug', summary='$summary', status=$status, content='$content' WHERE id=$id";
        }
        
        return $this->conn->query($sql);
    }
}
?>