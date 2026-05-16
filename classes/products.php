
<?php
    class Product {
        private $conn;
        public function __construct($db_connection) {
            $this->conn = $db_connection;
        }

        public function addProduct($category_id, $brand_id, $name, $slug, $image, $video, $power_capacity, $price, $old_price, $warranty, $stock, $short_description,$content, $status ){
            $name = mysqli_real_escape_string($this->conn, $name);
            $slug = mysqli_real_escape_string($this->conn, $slug);
            $short_description = mysqli_real_escape_string($this->conn, $short_description);
            $content = mysqli_real_escape_string($this->conn, $content);
            $power_capacity = mysqli_real_escape_string($this->conn, $power_capacity);
            $warranty = mysqli_real_escape_string($this->conn, $warranty);
            
            $video_sql = ($video != '') ? "'$video'" : "NULL";
            $brand_sql = ($brand_id != '') ? $brand_id : "NULL";
            $old_price_sql = ($old_price != '') ? $old_price : "NULL";
            $category_id = ($category_id != '') ? $category_id : "NULL";

            // Kiểm tra xem Slug (đường dẫn) đã tồn tại chưa để tránh trùng lặp
            $check_sql = "SELECT id FROM tbl_products WHERE slug = '$slug'";
            $check_res = mysqli_query($this->conn, $check_sql);
            if (mysqli_num_rows($check_res) > 0) {
                return "exists"; // Báo lỗi trùng
            }

            $sql = "INSERT INTO tbl_products 
                (category_id, brand_id, name, slug, image, video, power_capacity, price, old_price, warranty, stock, short_description, content, status) 
                VALUES 
                ($category_id, $brand_sql, '$name', '$slug', '$image', $video_sql, '$power_capacity', $price, $old_price_sql, '$warranty', $stock, '$short_description', '$content', $status)";

            if (mysqli_query($this->conn, $sql)) {
                return mysqli_insert_id($this->conn); 
            } else {
                die("SQL ERROR: " . mysqli_error($this->conn));
            }
        }

        public function addGalleryImage($product_id, $image_path) {
            $safe_product_id = (int)$product_id; 
            
            $sql = "INSERT INTO tbl_product_gallery (product_id, image_path) VALUES ($safe_product_id, '$image_path')";
            
            $result = mysqli_query($this->conn, $sql);
            
            if (!$result) {
                die("Lỗi Database: " . mysqli_error($this->conn) . " <br>Câu SQL đang bị lỗi là: " . $sql);
            }
        }

        public function getAllProducts(){
            $sql = "SELECT p.*, c.name as category_name, c.status as category_status, b.name as brand_name, b.logo as brand_logo, b.status as brand_status
                    FROM tbl_products p
                    LEFT JOIN tbl_categories c ON p.category_id = c.id
                    LEFT JOIN tbl_brands b ON p.brand_id = b.id
                    ORDER BY p.id DESC";
            $result = mysqli_query($this->conn, $sql);
            
            $products = [];
            if($result && mysqli_num_rows($result)>0){
                while ($row = mysqli_fetch_assoc($result)){
                    $products[] = $row;
                }
            }
            return $products;
        }
        public function deleteProduct($id) {
            $id = (int)$id;

            $query_main = "SELECT image, video FROM tbl_products WHERE id = ?";
            $stmt_main = mysqli_prepare($this->conn, $query_main);
            mysqli_stmt_bind_param($stmt_main, "i", $id);
            mysqli_stmt_execute($stmt_main);
            $result_main = mysqli_stmt_get_result($stmt_main);
            
            if ($row = mysqli_fetch_assoc($result_main)) {
                // Xóa ảnh đại diện (Bỏ qua nếu là ảnh mặc định)
                if (!empty($row['image']) && $row['image'] !== 'default.jpg') {
                    $img_path = "../../uploads/products/images/" . $row['image'];
                    if (file_exists($img_path)) {
                        unlink($img_path);
                    }
                }
                
                // Xóa file video (Nếu có)
                if (!empty($row['video'])) {
                    // Bạn nhớ tạo thư mục videos nếu lưu riêng nhé, hoặc sửa lại đường dẫn cho đúng nơi lưu
                    $video_path = "../../uploads/products/videos/" . $row['video']; 
                    if (file_exists($video_path)) {
                        unlink($video_path);
                    }
                }
            }
            mysqli_stmt_close($stmt_main);

            $query_gal = "SELECT image_path FROM tbl_product_gallery WHERE product_id = ?";
            $stmt_gal = mysqli_prepare($this->conn, $query_gal);
            if ($stmt_gal) {
                mysqli_stmt_bind_param($stmt_gal, "i", $id);
                mysqli_stmt_execute($stmt_gal);
                $result_gal = mysqli_stmt_get_result($stmt_gal);
                
                while ($gal = mysqli_fetch_assoc($result_gal)) {
                    if (!empty($gal['image_path'])) {
                        $gal_path = "../../uploads/products/image_gallery/" . $gal['image_path'];
                        if (file_exists($gal_path)) {
                            unlink($gal_path); // Xóa từng tấm ảnh phụ trên ổ cứng
                        }
                    }
                }
                mysqli_stmt_close($stmt_gal);
            }
            
            // Nhờ ON DELETE CASCADE, CSDL sẽ tự động xóa luôn các dòng thông số và gallery liên quan
            $sql = "DELETE FROM tbl_products WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                $result = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                return $result; // Trả về true nếu xóa thành công
            }
            return false;
        }
        // =========================================================
        // HÀM XÓA ẢNH PHỤ (DÙNG CHO CẢ AJAX VÀ XÓA SẢN PHẨM)
        // =========================================================
        public function deleteGalleryImage($gal_id) {
            $gal_id = (int)$gal_id;
            
            // 1. Tìm tên file ảnh trong Database
            $sql_find = "SELECT image_path FROM tbl_product_gallery WHERE id = $gal_id";
            $res = mysqli_query($this->conn, $sql_find);
            
            if($row = mysqli_fetch_assoc($res)) {
                // 2. Lùi 2 cấp (../../) để từ admin/actions/ chui ra đúng thư mục uploads
                $path = "../../uploads/products/image_gallery/" . $row['image_path'];
                
                // 3. Xóa file cứng trên ổ đĩa
                if(file_exists($path) && !is_dir($path)) {
                    unlink($path);
                }
            }
            
            // 4. Xóa dòng dữ liệu trong Database
            mysqli_query($this->conn, "DELETE FROM tbl_product_gallery WHERE id = $gal_id");
        }

        public function countActiveByCategory($productsArray, $catId) {
            $count = 0;
            foreach ($productsArray as $p) {
                if ($p['status'] == 1 && $p['category_id'] == $catId) {
                    $count++;
                }
            }
            return $count;
        }
        public function updateProduct($id, $category_id, $brand_id, $name, $slug, $image, $video, $power_capacity, $price, $old_price, $warranty, $stock, $short_description,$content, $status) {
            $id = (int)$id;
            $name = mysqli_real_escape_string($this->conn, $name);
            $slug = mysqli_real_escape_string($this->conn, $slug);
            $short_description = mysqli_real_escape_string($this->conn, $short_description);
            $content = mysqli_real_escape_string($this->conn, $content);
            $power_capacity = mysqli_real_escape_string($this->conn, $power_capacity);
            $warranty = mysqli_real_escape_string($this->conn, $warranty);

            $brand_sql = ($brand_id != '') ? (int)$brand_id : "NULL";
            $category_id = ($category_id != '') ? (int)$category_id : "NULL";
            $price = ($price != '') ? (int)$price : 0;
            $old_price_sql = ($old_price != '') ? (int)$old_price : "NULL";
            $stock = (int)$stock;
            $status = (int)$status;

            // Xử lý câu lệnh update ảnh (chỉ update nếu có file ảnh mới truyền vào)
            $image_query = "";
            if ($image != "") {
                $image = mysqli_real_escape_string($this->conn, $image);
                $image_query = ", image = '$image'";
            }

            $video_query = "";
            if ($video != "") {
                $video_query = ", video = '$video'";
            }

            $sql = "UPDATE tbl_products SET 
                    category_id = $category_id,
                    brand_id = $brand_sql,
                    name = '$name',
                    slug = '$slug',
                    power_capacity = '$power_capacity',
                    price = $price,
                    old_price = $old_price_sql,
                    warranty = '$warranty',
                    stock = $stock,
                    short_description = '$short_description',
                    content = '$content',
                    status = $status
                    $image_query
                    $video_query
                    WHERE id = $id";

            return mysqli_query($this->conn, $sql);
        }

        public function getTotalProducts() {
            $sql = "SELECT COUNT(id) as total FROM tbl_products";
            $result = mysqli_query($this->conn, $sql);
            $row = mysqli_fetch_assoc($result);
            return $row['total'];
        }

        public function getProductPaginated($limit, $offset) {
        $sql = "SELECT * FROM tbl_products ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $result = mysqli_query($this->conn, $sql);
        
        $categories = [];
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $categories[] = $row;
            }
        }
        return $categories;
    }
    }
?>