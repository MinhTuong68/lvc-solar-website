<?php
    class Project{
        private $conn;

        public function __construct($db_connection) {
            $this->conn = $db_connection;
        }

        public function addProject($name, $slug, $client, $completion_date, $location, $capacity, $description, $image, $is_featured, $status, $content) {
            // Chống SQL Injection
            $name = mysqli_real_escape_string($this->conn, $name);
            $slug = mysqli_real_escape_string($this->conn, $slug);
            $client = mysqli_real_escape_string($this->conn, $client);
            $location = mysqli_real_escape_string($this->conn, $location);
            $capacity = mysqli_real_escape_string($this->conn, $capacity);
            $description = mysqli_real_escape_string($this->conn, $description);
            $image = mysqli_real_escape_string($this->conn, $image);
            $content = mysqli_real_escape_string($this->conn, $content);
            
            $is_featured = (int)$is_featured;
            $status = (int)$status;

            // Xử lý ngày tháng (nếu để trống thì gán NULL để không bị lỗi CSDL)
            $date_sql = ($completion_date != '') ? "'".mysqli_real_escape_string($this->conn, $completion_date)."'" : "NULL";

            // Kiểm tra xem Slug (đường dẫn) đã tồn tại chưa
            $check_sql = "SELECT id FROM tbl_projects WHERE slug = '$slug'";
            $check_res = mysqli_query($this->conn, $check_sql);
            if (mysqli_num_rows($check_res) > 0) {
                return "exists"; 
            }

            // Câu lệnh SQL thêm vào Database
            $sql = "INSERT INTO tbl_projects (name, slug, client, completion_date, location, capacity, description, image, is_featured, status, content) 
                    VALUES ('$name', '$slug', '$client', $date_sql, '$location', '$capacity', '$description', '$image', $is_featured, $status, '$content')";

            if (mysqli_query($this->conn, $sql)) {
                // Trả về ID của dự án vừa thêm để tiếp tục thêm ảnh vào thư viện (Gallery)
                return mysqli_insert_id($this->conn); 
            } else {
                die("LỖI DATABASE: " . mysqli_error($this->conn) . "<br><br>CÂU LỆNH BỊ LỖI: " . $sql);
            }
        }

        // 2. HÀM THÊM ẢNH VÀO THƯ VIỆN ẢNH (GALLERY)
        public function addProjectGallery($project_id, $image) {
            $project_id = (int)$project_id;
            $image = mysqli_real_escape_string($this->conn, $image);
            
            $sql = "INSERT INTO tbl_project_gallery (project_id, image) VALUES ($project_id, '$image')";
            return mysqli_query($this->conn, $sql);
        }

        // 3. HÀM LẤY TẤT CẢ DỰ ÁN (Dùng cho trang Quản lý / Danh sách)
        public function getAllProjects($status = 'all') {
            $sql = "SELECT * FROM tbl_projects WHERE 1=1";
            
            // Nếu muốn lọc theo trạng thái (Ví dụ chỉ lấy những bài đang Hiện)
            if ($status !== 'all') {
                $status = (int)$status;
                $sql .= " AND status = $status";
            }

            // Sắp xếp dự án mới nhất lên đầu
            $sql .= " ORDER BY id DESC";
            
            $result = mysqli_query($this->conn, $sql);
            
            $projects = [];
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $projects[] = $row;
                }
            }
            return $projects;
        }

          public function getProjectBySlug($slug) {
            $slug = $this->conn->real_escape_string($slug);
            
            // Tăng lượt xem (views) lên 1 mỗi khi có người đọc
            $updateView = "UPDATE tbl_projects SET views = views + 1 WHERE slug = '$slug'";
            $this->conn->query($updateView);

            // Lấy dữ liệu bài viết ra
            $sql = "SELECT * FROM tbl_projects WHERE slug = '$slug' AND status = 1 LIMIT 1";
            $result = $this->conn->query($sql);
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            return null;
        }

        public function getProjectGallery($project_id) {
            $project_id = (int)$project_id;
            
            // Lấy ảnh phụ, sắp xếp theo sort_order hoặc mới nhất
            $sql = "SELECT * FROM tbl_project_gallery WHERE project_id = $project_id ORDER BY sort_order ASC, id DESC";
            $result = mysqli_query($this->conn, $sql);
            
            $gallery = [];
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $gallery[] = $row;
                }
            }
            return $gallery;
        }

        // HÀM BẬT/TẮT TRẠNG THÁI NỔI BẬT (FEATURED)
        public function toggleFeatured($id, $current_featured) {
            $id = (int)$id;
            $new_featured = ($current_featured == 1) ? 0 : 1; // Nếu là 1 thì thành 0, và ngược lại
            $sql = "UPDATE tbl_projects SET is_featured = $new_featured WHERE id = $id";
            return mysqli_query($this->conn, $sql);
        }
        
        public function getPopularProjects($limit = 5) {
            // Lọc các dự án đang hoạt động và sắp xếp giảm dần theo lượt views
            $sql = "SELECT * FROM tbl_projects WHERE status = 1 ORDER BY views DESC LIMIT ?";
            
            $stmt = $this->conn->prepare($sql);
            $projects = [];
            
            if ($stmt) {
                // 'i' đại diện cho tham số kiểu số nguyên (integer)
                $stmt->bind_param("i", $limit);
                $stmt->execute();
                
                // Lấy kết quả trả về
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $projects[] = $row;
                    }
                }
            }
            
            return $projects;
        }

        // HÀM XÓA DỰ ÁN (Xóa cả dữ liệu DB và ảnh vật lý)
        public function deleteProject($id) {
            $id = (int)$id;

            // 1. Lấy thông tin dự án để xóa file ảnh chính
            $sql_get = "SELECT image FROM tbl_projects WHERE id = $id";
            $result = mysqli_query($this->conn, $sql_get);
            if ($row = mysqli_fetch_assoc($result)) {
                $img_path = "../../uploads/projects/images/" . $row['image'];
                if (!empty($row['image']) && file_exists($img_path)) {
                    unlink($img_path); // Xóa file ảnh gốc
                }
            }

            // 2. Lấy thông tin và xóa các file ảnh trong thư viện (Gallery)
            $sql_gal = "SELECT image FROM tbl_project_gallery WHERE project_id = $id";
            $res_gal = mysqli_query($this->conn, $sql_gal);
            while ($row_gal = mysqli_fetch_assoc($res_gal)) {
                $gal_path = "../../uploads/projects/gallery/" . $row_gal['image'];
                if (!empty($row_gal['image']) && file_exists($gal_path)) {
                    unlink($gal_path); // Xóa file ảnh phụ
                }
            }
            // Xóa các dòng dữ liệu của thư viện ảnh trong DB
            mysqli_query($this->conn, "DELETE FROM tbl_project_gallery WHERE project_id = $id");

            // 3. Cuối cùng, xóa dữ liệu dự án trong DB
            $sql_del = "DELETE FROM tbl_projects WHERE id = $id";
            if (mysqli_query($this->conn, $sql_del)) {
                return true;
            }
            return false;
        }

        // 1. Lấy chi tiết dự án theo ID để đổ dữ liệu vào form sửa
        public function getProjectByID($id) {
            $id = (int)$id;
            $sql = "SELECT * FROM tbl_projects WHERE id = $id";
            $result = mysqli_query($this->conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                return mysqli_fetch_assoc($result);
            }
            return null;
        }

        // 2. Hàm cập nhật thông tin dự án
        public function updateProject($id, $name, $slug, $client, $completion_date, $location, $capacity, $description, $image, $is_featured, $status, $content) {
            $id = (int)$id;
            $name = mysqli_real_escape_string($this->conn, $name);
            $slug = mysqli_real_escape_string($this->conn, $slug);
            $client = mysqli_real_escape_string($this->conn, $client);
            $location = mysqli_real_escape_string($this->conn, $location);
            $capacity = mysqli_real_escape_string($this->conn, $capacity);
            $description = mysqli_real_escape_string($this->conn, $description);
            $content = mysqli_real_escape_string($this->conn, $content);
            $is_featured = (int)$is_featured;
            $status = (int)$status;
            $date_sql = ($completion_date != '') ? "'" . mysqli_real_escape_string($this->conn, $completion_date) . "'" : "NULL";

            if ($image != "") {
                $image = mysqli_real_escape_string($this->conn, $image);
                $sql = "UPDATE tbl_projects SET 
                        name='$name', slug='$slug', client='$client', completion_date=$date_sql, 
                        location='$location', capacity='$capacity', description='$description', 
                        image='$image', is_featured=$is_featured, status=$status, content='$content' 
                        WHERE id=$id";
            } else {
                $sql = "UPDATE tbl_projects SET 
                        name='$name', slug='$slug', client='$client', completion_date=$date_sql, 
                        location='$location', capacity='$capacity', description='$description', 
                        is_featured=$is_featured, status=$status, content='$content' 
                        WHERE id=$id";
            }
            return mysqli_query($this->conn, $sql);
        }
    }
?>