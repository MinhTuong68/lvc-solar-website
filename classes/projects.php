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
    }
?>