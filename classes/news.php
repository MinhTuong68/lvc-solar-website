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
}
?>