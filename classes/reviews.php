<?php
class Review {
    private $conn;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // 1. THÊM ĐÁNH GIÁ MỚI VÀO DB
    public function addReview($product_id, $customer_name, $customer_email, $rating, $content, $images_str) {
        $product_id = (int)$product_id;
        $rating = (int)$rating;

        $config_file = __DIR__ . '/../admin/review_settings.json';
        if (file_exists($config_file)) {
            $allowed_stars = json_decode(file_get_contents($config_file), true);
            if (!in_array((string)$rating, $allowed_stars)) {
                return false; 
            }
        }

        $customer_name = mysqli_real_escape_string($this->conn, trim($customer_name));
        $customer_email = mysqli_real_escape_string($this->conn, trim($customer_email));
        $content = mysqli_real_escape_string($this->conn, trim($content));

        $sql = "INSERT INTO tbl_reviews (product_id, customer_name, customer_email, rating, content, images, status) 
                VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ississ", $product_id, $customer_name, $customer_email, $rating, $content, $images_str);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    // 2. TỐ CÁO ĐÁNH GIÁ (BÁO CÁO VI PHẠM)
    public function reportReview($review_id, $reason, $reporter_ip) {
        $review_id = (int)$review_id;
        $reason = mysqli_real_escape_string($this->conn, trim($reason));
        $reporter_ip = mysqli_real_escape_string($this->conn, trim($reporter_ip));

        $sql = "INSERT INTO tbl_review_reports (review_id, reason, reporter_ip, status) VALUES (?, ?, ?, 0)";
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iss", $review_id, $reason, $reporter_ip);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    // 3. LẤY TẤT CẢ ĐÁNH GIÁ CỦA 1 SẢN PHẨM & TÍNH TOÁN THỐNG KÊ (SAO TRUNG BÌNH, PHẦN TRĂM)
    public function getReviewsAndStatsByProduct($product_id) {
        $product_id = (int)$product_id;
        
        $sql = "SELECT * FROM tbl_reviews WHERE product_id = $product_id AND status = 1 ORDER BY created_at DESC";
        $result = mysqli_query($this->conn, $sql);
        
        $reviews = [];
        $total_reviews = 0;
        $total_stars = 0;
        $star_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        if ($result && mysqli_num_rows($result) > 0) {
            $total_reviews = mysqli_num_rows($result);
            while ($row = mysqli_fetch_assoc($result)) {
                $reviews[] = $row;
                $total_stars += $row['rating'];
                $star_counts[$row['rating']]++;
            }
        }

        // Tính toán thông kê
        $avg_rating = $total_reviews > 0 ? round($total_stars / $total_reviews, 1) : 0;
        $star_percents = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        
        if ($total_reviews > 0) {
            foreach ($star_counts as $star => $count) {
                $star_percents[$star] = round(($count / $total_reviews) * 100);
            }
        }

        // Trả về một mảng chứa cả Danh sách review và Bảng thống kê sao
        return [
            'list' => $reviews,
            'total' => $total_reviews,
            'avg_rating' => $avg_rating,
            'star_percents' => $star_percents
        ];
    }
    public function checkReviewExists($product_id, $customer_email) {
        $product_id = (int)$product_id;
        $customer_email = mysqli_real_escape_string($this->conn, trim($customer_email));
        
        $sql = "SELECT id FROM tbl_reviews WHERE product_id = ? AND customer_email = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "is", $product_id, $customer_email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt); // Lưu kết quả để đếm số dòng
            $count = mysqli_stmt_num_rows($stmt);
            mysqli_stmt_close($stmt);
            
            return $count > 0; // Trả về true nếu đã tồn tại, false nếu chưa
        }
        return false;
    }

    // ================= CÁC HÀM QUẢN TRỊ ĐÁNH GIÁ (ADMIN) =================

    // 1. Xóa 1 đánh giá (bằng ID)
    public function deleteReview($id) {
        $id = (int)$id;
        // B1: Xóa file ảnh vật lý
        $res_img = $this->conn->query("SELECT images FROM tbl_reviews WHERE id = $id");
        if($res_img && $res_img->num_rows > 0) {
            $row = $res_img->fetch_assoc();
            if(!empty($row['images'])) {
                $images = explode(',', $row['images']);
                foreach($images as $img) {
                    $img_path = "../uploads/products/reviews/" . trim($img);
                    if(file_exists($img_path) && is_file($img_path)) unlink($img_path);
                }
            }
        }
        // B2: Xóa dữ liệu DB
        $stmt = $this->conn->prepare("DELETE FROM tbl_reviews WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // 2. Ẩn / Hiện đánh giá
    public function toggleStatus($id, $current_status) {
        $id = (int)$id;
        $new_status = $current_status == 1 ? 0 : 1;
        $stmt = $this->conn->prepare("UPDATE tbl_reviews SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $id);
        return $stmt->execute();
    }

    // 3. Xóa hàng loạt theo Sao (Dọn rác)
    public function bulkDeleteByStar($max_star) {
        $max_star = (int)$max_star;
        // B1: Xóa file ảnh vật lý của các đánh giá sắp bị xóa
        $stmt_img = $this->conn->prepare("SELECT images FROM tbl_reviews WHERE rating <= ?");
        $stmt_img->bind_param("i", $max_star);
        $stmt_img->execute();
        $res_img = $stmt_img->get_result();
        
        while($row = $res_img->fetch_assoc()) {
            if(!empty($row['images'])) {
                $images = explode(',', $row['images']);
                foreach($images as $img) {
                    $img_path = "../uploads/products/reviews/" . trim($img);
                    if(file_exists($img_path) && is_file($img_path)) unlink($img_path);
                }
            }
        }
        // B2: Xóa dữ liệu DB
        $stmt = $this->conn->prepare("DELETE FROM tbl_reviews WHERE rating <= ?");
        $stmt->bind_param("i", $max_star);
        return $stmt->execute();
    }
}
?>