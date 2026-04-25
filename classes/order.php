<?php
    class Order{
        private $conn;

        public function __construct($conn){
            $this->conn = $conn;
        }
       /**
     * HÀM TẠO ĐƠN HÀNG (Dùng Transaction để chống lỗi rớt mạng)
     * @param array $customerInfo - Mảng chứa thông tin khách hàng
     * @param array $cartItems - Mảng chứa danh sách sản phẩm
     * @return bool - true nếu thành công, false nếu thất bại
     */
    
        public function createOrder($customerInfo, $cartItems){
            try{
                $this->conn->begin_transaction();

                $order_code = $this->conn->real_escape_string($customerInfo['order_code']);
                $name       = $this->conn->real_escape_string($customerInfo['name']);
                $phone      = $this->conn->real_escape_string($customerInfo['phone']);
                $email      = isset($customerInfo['email']) ? $this->conn->real_escape_string($customerInfo['email']) : '';
                $address    = $this->conn->real_escape_string($customerInfo['address']);
                $note       = isset($customerInfo['note']) ? $this->conn->real_escape_string($customerInfo['note']) : '';
                $payment    = $this->conn->real_escape_string($customerInfo['payment_method']);
                $total      = (float)$customerInfo['total_amount'];

                $sql_order = "INSERT INTO tbl_orders (order_code, customer_name, customer_phone, customer_email, customer_address, note, payment_method, payment_status, total_amount, status) 
                            VALUES ('$order_code', '$name', '$phone', '$email', '$address', '$note', '$payment', 'unpaid', $total, 'new')";

                if (!$this->conn->query($sql_order)) {
                    throw new Exception("Lỗi thêm đơn hàng: " . $this->conn->error);
                }

                // Lấy ID của đơn hàng vừa tạo để làm khóa ngoại (order_id)
                $order_id = $this->conn->insert_id;
                foreach ($cartItems as $item) {
                    $product_id   = (int)$item['id'];
                    $product_name = $this->conn->real_escape_string($item['name']); // Lưu luôn tên SP đề phòng sau này đổi tên
                    $price        = (float)$item['price']; // Lưu giá chốt lúc mua
                    $qty          = (int)$item['quantity'];

                    $sql_detail = "INSERT INTO tbl_order_details (order_id, product_id, product_name, price, quantity) 
                                VALUES ($order_id, $product_id, '$product_name', $price, $qty)";
                    
                    if (!$this->conn->query($sql_detail)) {
                        throw new Exception("Lỗi thêm chi tiết sản phẩm: " . $this->conn->error);
                    }
                    $sql_update_stats = "UPDATE tbl_products SET sold = sold + $qty, stock = stock - $qty WHERE id = $product_id";
                    $this->conn->query($sql_update_stats);
                }
                $this->conn->commit();
                return true;
            }catch (Exception $e) {
                // Có lỗi -> Hủy toàn bộ thao tác vừa làm, không lưu gì cả
                $this->conn->rollback();
                // (Tùy chọn) In lỗi ra file log để debug: error_log($e->getMessage());
                return false;
            }
        }
        public function getOrderByCode($order_code) {
            $order_code = $this->conn->real_escape_string($order_code);
            $sql = "SELECT * FROM tbl_orders WHERE order_code = '$order_code' LIMIT 1";
            $result = $this->conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            return null;
        }

        public function getOrderDetails($order_id) {
            $order_id = (int)$order_id;
            $sql = "SELECT * FROM tbl_order_details WHERE order_id = $order_id";
            $result = $this->conn->query($sql);
            
            $items = [];
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $items[] = $row;
                }
            }
            return $items;
        }
        public function getAllOrders($status = 'all', $search = '') {
            $where = "WHERE 1=1";
            
            // 1. Lọc theo trạng thái
            if ($status != 'all') {
                $status_esc = $this->conn->real_escape_string($status);
                $where .= " AND status = '$status_esc'";
            }
            
            // 2. Tìm kiếm theo Tên, SĐT hoặc Mã đơn
            if ($search != '') {
                $search_esc = $this->conn->real_escape_string($search);
                $where .= " AND (customer_name LIKE '%$search_esc%' OR customer_phone LIKE '%$search_esc%' OR order_code LIKE '%$search_esc%')";
            }
            
            // 3. Truy vấn bảng tbl_orders (sắp xếp mới nhất lên đầu)
            $sql = "SELECT * FROM tbl_orders $where ORDER BY id DESC";
            $result = $this->conn->query($sql);
            
            $orders = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $orders[] = $row;
                }
            }
            return $orders;
        }
        // public function updateOrderStatus($order_id, $status) {
        //     $order_id = (int)$order_id;
        //     $status = $this->conn->real_escape_string($status);
        //     $sql_old = "SELECT status FROM tbl_orders WHERE id = $order_id";
        //     $res_old = $this->conn->query($sql_old);
        //     $sql = "UPDATE tbl_orders SET status = '$status' WHERE id = $order_id";
        //     return $this->conn->query($sql);
        // }
        public function updateOrderStatus($order_id, $status) {
            $order_id = (int)$order_id;
            $status = $this->conn->real_escape_string($status);

            // 1. LẤY TRẠNG THÁI CŨ
            $sql_old = "SELECT status FROM tbl_orders WHERE id = $order_id";
            $res_old = $this->conn->query($sql_old);
            
            if ($res_old && $res_old->num_rows > 0) {
                $old_status = $res_old->fetch_assoc()['status'];

                // 2. CHỐT CHẶN LOGIC KHO HÀNG
                if ($old_status != 'cancelled' && $status == 'cancelled') {
                    // Bị Hủy -> Trả hàng về kho
                    $this->handleOrderStock($order_id, 'restore');
                }elseif ($old_status == 'cancelled' && $status != 'cancelled') {
                    // --- BẮT ĐẦU CHỐT CHẶN KHÔI PHỤC ---
                    $stock_check = $this->checkStockSufficient($order_id);
                    if ($stock_check !== true) {
                        // Kích hoạt Toast Message báo lỗi (Sử dụng hệ thống Toast có sẵn của Admin)
                        $_SESSION['toast_message'] = "Lỗi khôi phục! Kho không đủ sản phẩm: [ $stock_check ]";
                        $_SESSION['toast_type'] = 'error';
                        
                        // Lệnh Header này ép trình duyệt load mới 100% (Xóa bỏ bộ nhớ đệm Form Cache)
                        $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "index.php?page=manage-order";
                        header("Location: " . $redirect_url);
                        exit; // Ngắt ngang lệnh Update
                    }
                    // --- KẾT THÚC CHỐT CHẶN ---

                    // Bị lỡ tay hủy, nay được khôi phục & KHO ĐỦ HÀNG -> Lấy hàng xuất đi
                    $this->handleOrderStock($order_id, 'deduct');
                }
            }

            // 3. Cập nhật trạng thái mới
            $sql = "UPDATE tbl_orders SET status = '$status' WHERE id = $order_id";
            return $this->conn->query($sql);
        }
        public function getOrderByID($id) {
            $id = (int)$id;
            $sql = "SELECT * FROM tbl_orders WHERE id = $id LIMIT 1";
            $result = $this->conn->query($sql);
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            return null;
        }
        // public function updateOrder($id, $status, $payment_status, $note) {
        //     $id = (int)$id;
        //     $status = $this->conn->real_escape_string($status);
        //     $payment_status = $this->conn->real_escape_string($payment_status);
        //     $note = $this->conn->real_escape_string($note);
            
        //     $sql = "UPDATE tbl_orders SET 
        //             status = '$status', 
        //             payment_status = '$payment_status', 
        //             note = '$note' 
        //             WHERE id = $id";
        //     return $this->conn->query($sql);
        // }
        public function updateOrder($id, $status, $payment_status, $note) {
            $id = (int)$id;
            $status = $this->conn->real_escape_string($status);
            $payment_status = $this->conn->real_escape_string($payment_status);
            $note = $this->conn->real_escape_string($note);
            
            // 1. LẤY TRẠNG THÁI CŨ
            $sql_old = "SELECT status FROM tbl_orders WHERE id = $id";
            $res_old = $this->conn->query($sql_old);
            
            if ($res_old && $res_old->num_rows > 0) {
                $old_status = $res_old->fetch_assoc()['status'];

                // 2. CHỐT CHẶN LOGIC KHO HÀNG
                if ($old_status != 'cancelled' && $status == 'cancelled') {
                    $this->handleOrderStock($id, 'restore');
                } elseif ($old_status == 'cancelled' && $status != 'cancelled') {
                    $stock_check = $this->checkStockSufficient($id); // Chú ý: Ở hàm này biến truyền vào là $id
                    if ($stock_check !== true) {
                        // Kích hoạt Toast Message báo lỗi
                        $_SESSION['toast_message'] = "Lỗi khôi phục! Kho không đủ sản phẩm: [ $stock_check ]";
                        $_SESSION['toast_type'] = 'error';
                        
                        // Ép tải mới
                        $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "index.php?page=order-detail&id=" . $id;
                        header("Location: " . $redirect_url);
                        exit;
                    }
                    // --- KẾT THÚC CHỐT CHẶN ---
                    $this->handleOrderStock($id, 'deduct');
                }
            }

            // 3. Cập nhật thông tin mới
            $sql = "UPDATE tbl_orders SET 
                    status = '$status', 
                    payment_status = '$payment_status', 
                    note = '$note' 
                    WHERE id = $id";
            return $this->conn->query($sql);
        }

        /**
         * THÊM SẢN PHẨM VÀO ĐƠN HÀNG (CÓ TRỪ KHO & GỘP MÓN)
         */
        public function addProductToOrder($order_id, $product_id, $qty) {
            $order_id = (int)$order_id;
            $product_id = (int)$product_id;
            $qty = (int)$qty;

            // 1. Lấy thông tin sản phẩm và kiểm tra tồn kho
            $sql_prod = "SELECT name, price, stock FROM tbl_products WHERE id = $product_id";
            $res_prod = $this->conn->query($sql_prod);
            
            if (!$res_prod || $res_prod->num_rows == 0) {
                return ['status' => false, 'msg' => 'Lỗi: Sản phẩm không tồn tại!'];
            }
            
            $sp = $res_prod->fetch_assoc();
            
            if ($sp['stock'] < $qty) {
                return ['status' => false, 'msg' => 'Lỗi: Số lượng trong kho không đủ!'];
            }

            $p_name = $this->conn->real_escape_string($sp['name']);
            $p_price = (float)$sp['price'];

            // 2. Kiểm tra xem món này đã có trong đơn chưa
            $sql_check = "SELECT id FROM tbl_order_details WHERE order_id = $order_id AND product_id = $product_id";
            $res_check = $this->conn->query($sql_check);

            if ($res_check && $res_check->num_rows > 0) {
                // Đã có -> Cộng dồn số lượng
                $this->conn->query("UPDATE tbl_order_details SET quantity = quantity + $qty WHERE order_id = $order_id AND product_id = $product_id");
            } else {
                // Chưa có -> Thêm dòng mới
                $this->conn->query("INSERT INTO tbl_order_details (order_id, product_id, product_name, price, quantity) VALUES ($order_id, $product_id, '$p_name', $p_price, $qty)");
            }

            // 3. Cộng tiền vào tổng đơn hàng
            $total_add = $p_price * $qty;
            $this->conn->query("UPDATE tbl_orders SET total_amount = total_amount + $total_add WHERE id = $order_id");

            // 4. Trừ đi số lượng tồn kho VÀ Tăng lượt bán
            $this->conn->query("UPDATE tbl_products SET stock = stock - $qty, sold = sold + $qty WHERE id = $product_id");         

            return ['status' => true, 'msg' => "Đã thêm $qty '$p_name' vào đơn!"];
        }

        /**
         * XÓA ĐƠN HÀNG (Kèm theo trả lại số lượng kho và xóa chi tiết)
         */
        public function deleteOrder($order_id) {
            $order_id = (int)$order_id;
            
            try {
                // Bắt đầu chuỗi hành động (Nếu 1 bước lỗi, hủy toàn bộ)
                $this->conn->begin_transaction();

                // 1. Trả lại số lượng tồn kho (Hoàn kho)
                $sql_get_items = "SELECT product_id, quantity FROM tbl_order_details WHERE order_id = $order_id";
                $res_items = $this->conn->query($sql_get_items);
                
                if ($res_items && $res_items->num_rows > 0) {
                    while ($item = $res_items->fetch_assoc()) {
                        $pid = $item['product_id'];
                        $qty = $item['quantity'];
                        // Cộng lại số lượng vào kho VÀ trừ đi lượt bán
                        $this->conn->query("UPDATE tbl_products SET stock = stock + $qty, sold = sold - $qty WHERE id = $pid");
                    }
                }

                // 2. Xóa tất cả các món hàng trong chi tiết đơn
                $sql_del_details = "DELETE FROM tbl_order_details WHERE order_id = $order_id";
                if (!$this->conn->query($sql_del_details)) {
                    throw new Exception("Không thể xóa chi tiết đơn hàng.");
                }

                // 3. Xóa đơn hàng chính
                $sql_del_order = "DELETE FROM tbl_orders WHERE id = $order_id";
                if (!$this->conn->query($sql_del_order)) {
                    throw new Exception("Không thể xóa thông tin đơn hàng.");
                }

                // Nếu mọi thứ trơn tru -> Lưu thay đổi
                $this->conn->commit();
                return ['status' => true, 'msg' => 'Đã xóa đơn hàng và hoàn lại số lượng kho thành công!'];

            } catch (Exception $e) {
                // Nếu có lỗi -> Phục hồi lại dữ liệu như cũ
                $this->conn->rollback();
                return ['status' => false, 'msg' => 'Lỗi khi xóa: ' . $e->getMessage()];
            }
        }

        /**
     * HÀM HỖ TRỢ XỬ LÝ CỘNG/TRỪ KHO KHI ĐỔI TRẠNG THÁI
     */
        private function handleOrderStock($order_id, $action) {
            $sql_items = "SELECT product_id, quantity FROM tbl_order_details WHERE order_id = $order_id";
            $res_items = $this->conn->query($sql_items);
            
            if ($res_items && $res_items->num_rows > 0) {
                while ($item = $res_items->fetch_assoc()) {
                    $pid = $item['product_id'];
                    $qty = $item['quantity'];
                    
                    if ($action == 'restore') {
                        // HOÀN KHO: Cộng số lượng lại vào kho, trừ số lượt bán đi
                        $this->conn->query("UPDATE tbl_products SET stock = stock + $qty, sold = sold - $qty WHERE id = $pid");
                    } elseif ($action == 'deduct') {
                        // TRỪ KHO: Trừ số lượng kho, cộng thêm lượt bán
                        $this->conn->query("UPDATE tbl_products SET stock = stock - $qty, sold = sold + $qty WHERE id = $pid");
                    }
                }
            }
        }

        /**
         * KIỂM TRA XEM KHO CÒN ĐỦ HÀNG ĐỂ KHÔI PHỤC ĐƠN KHÔNG
         */
        private function checkStockSufficient($order_id) {
            $sql = "SELECT d.product_id, d.quantity, p.stock, p.name 
                    FROM tbl_order_details d 
                    JOIN tbl_products p ON d.product_id = p.id 
                    WHERE d.order_id = $order_id";
            $res = $this->conn->query($sql);
            
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    // Nếu số lượng tồn kho NHỎ HƠN số lượng cần để khôi phục đơn
                    if ($row['stock'] < $row['quantity']) {
                        return $row['name']; // Báo động: Trả về tên cái sản phẩm đang bị thiếu
                    }
                }
            }
            return true; // An toàn: Kho dư sức đáp ứng
        }
    }
?>