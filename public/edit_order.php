<?php
    include("../classes/order.php");
    $orderObj = new Order($conn);

    if (!isset($_GET['id'])) {
        header("Location: ?page=order_history");
        exit;
    }

    $id = (int)$_GET['id'];
    $order = $orderObj->getOrderByID($id);

    // 2. Kiểm tra bảo mật: Đơn hàng phải tồn tại và thuộc về thiết bị này
    $authorized_phones = [];
    if (isset($_SESSION['authorized_phones'])) {
        $authorized_phones = $_SESSION['authorized_phones'];
    } else {
        $authorized_phones = [];
    }

    if (!$order || !in_array($order['customer_phone'], $authorized_phones)) {
        $_SESSION['toast_message'] = "Bạn không có quyền chỉnh sửa đơn hàng này!";
        $_SESSION['toast_type'] = "error";
        header("Location: ?page=order_history");
        exit;
    }

    if ($order['status'] !== 'new') {
        $_SESSION['toast_message'] = "Đơn hàng đã được xác nhận hoặc đã hủy, không thể sửa!";
        $_SESSION['toast_type'] = "error";
        header("Location: ?page=order_history");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        if (!isset($_POST['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['toast_message'] = "Yêu cầu không hợp lệ!";
            $_SESSION['toast_type'] = 'error';
            header("Location: ?page=edit_order&id=" . $id);
            exit;
        }

        $name    = e(trim($_POST['name'] ?? ''));
        $address = e(trim($_POST['address'] ?? ''));
        $note    = e(trim($_POST['note'] ?? ''));
         if (empty($name)) {
            $_SESSION['toast_message'] = "Vui lòng nhập họ tên!";
            $_SESSION['toast_type'] = 'error';
            header("Location: ?page=edit_order&id=" . $id);
            exit;
        }

        $result = $orderObj->clientUpdateOrder($id, $name, $address, $note);

        if($result){
            $_SESSION['toast_message'] = "Cập nhật thông tin đơn hàng thành công!";
            $_SESSION['toast_type'] = "success";
            header("Location: ?page=order_history");
            exit;
        }
        else{
            $_SESSION['toast_message'] = "Có lỗi xảy ra, vui lòng thử lại!";
            $_SESSION['toast_type'] = "error";
        }
    }

    $details = $orderObj->getOrderDetails($id);
?>
<div class="breadcrumb-bar">
    <div class="container">
        <div class="breadcrumb">
            <a href="?page=home">Trang chủ</a>
            <span class="sep">/</span>
            <a href="?page=order_history">Lịch sử đơn hàng</a>
            <span class="sep">/</span>
            <span class="current">Chỉnh sửa đơn hàng #<?= e($order['order_code']) ?></span>
        </div>
    </div>
</div>

<div class="edit-order-wrapper">
    <div class="container">
        <div class="edit-order-grid">
            
            <div class="edit-order-main">
                <div class="section-card">
                    <h2 class="edit-section-title">
                        <i class="fa-solid fa-user-pen"></i> Thông tin nhận hàng
                    </h2>
                    <p class="edit-section-subtitle">Bạn chỉ có thể sửa thông tin khi đơn hàng ở trạng thái "Chờ xác nhận".</p>
                    
                    <form action="" method="POST" class="styled-form" onsubmit="showLoading('Đang gửi yêu cầu, vui lòng đợi...');">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Họ và tên khách hàng</label>
                                <input type="text" name="name" value="<?= e($order['customer_name']) ?>" placeholder="Nhập họ tên...">
                            </div>
                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <input type="text" name="phone" value="<?= e($order['customer_phone']) ?>" readonly class="input-readonly">
                                <small>* Không thể thay đổi số điện thoại tra cứu</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Địa chỉ nhận hàng</label>
                            <textarea name="address" rows="3" placeholder="Địa chỉ cụ thể..."><?= e($order['customer_address']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Ghi chú đơn hàng</label>
                            <textarea name="note" rows="2" placeholder="Yêu cầu đặc biệt nếu có..."><?= e($order['note']) ?></textarea>
                        </div>

                        <div class="form-actions">
                            <a href="?page=order_history" class="btn-edit-outline">
                                <i class="fa-solid fa-arrow-left"></i> Quay lại
                            </a>
                            <button type="submit" class="btn-update">
                                <i class="fa-solid fa-check"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="edit-order-sidebar">
                <div class="section-card summary-card">
                    <h2 class="section-title">Sản phẩm trong đơn</h2>
                    <div class="order-items-list">
                        <?php
                            foreach($details as $item){
                                ?>  
                                    <div class="mini-product">
                                        <div class="p-img">
                                            <img src="<?php echo ROOT_URL ?>uploads/products/images/<?php echo $item['product_image'] ?>" alt="">
                                            <span class="p-qty"><?= $item['quantity'] ?></span>
                                        </div>
                                        <div class="p-info">
                                            <div class="p-name"><?= e($item['product_name']) ?></div>
                                            <div class="p-price"><?php echo number_format($item['price'], 0, ',', '.') ?>₫</div>
                                        </div>
                                    </div>

                                    <form action="actions/process-edit-order.php" class="action-qty-group" method="POST" style="display: flex; gap: 10px; align-items: center; margin-top: 10px;">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="order_id" value="<?= $id ?>">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">

                                        <div class="pd-qty-control" style="display: flex; align-items: center; border: 1.5px solid var(--gray-200); border-radius: var(--radius); overflow: hidden; height: 35px;">
                                            <button type="button" onclick="this.nextElementSibling.stepDown()" style="width: 35px; height: 100%; background: var(--gray-100); border: none; cursor: pointer; font-weight: bold;">-</button>
                                            
                                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" style="width: 45px; height: 100%; text-align: center; border: none; outline: none; font-weight: 600; -moz-appearance: textfield;">
                                            
                                            <button type="button" onclick="this.previousElementSibling.stepUp()" style="width: 35px; height: 100%; background: var(--gray-100); border: none; cursor: pointer; font-weight: bold;">+</button>
                                        </div>

                                        <button type="submit" name="action" value="update_qty" class="btn-update" style="height: 35px; padding: 0 15px; background: var(--navy); color: #fff; border: none; border-radius: var(--radius); cursor: pointer; font-size: 0.85rem; font-weight: 600; transition: 0.2s;">
                                            <i class="fa-solid fa-arrows-rotate"></i> Cập nhật
                                        </button>

                                        <button type="submit" name="action" value="delete_item" class="btn-delete" title="Xóa sản phẩm này" style="height: 35px; padding: 0 12px; background: #ef4444; color: #fff; border: none; border-radius: var(--radius); cursor: pointer; font-size: 0.85rem; transition: 0.2s;">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>

                                    </form>
                                <?php
                            }
                        ?>
                        

                    </div>

                    <div class="order-summary-total">
                        <div class="edit-summary-row">
                            <span>Tạm tính:</span>
                            <span><?php echo number_format($order['total_amount'], 0, ',', '.') ?>₫</span>
                        </div>
                        <div class="edit-summary-row">
                            <span>Vận chuyển:</span>
                            <span class="text-free">Miễn phí</span>
                        </div>
                        <div class="edit-summary-row final-total">
                            <span>Tổng cộng:</span>
                            <span class="price-total"><?php echo number_format($order['total_amount'], 0, ',', '.') ?>₫</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function updateQty(change, inputId = 'pdQuantity') {
        let qtyInput = document.getElementById(inputId);
        if (!qtyInput) return; // Không tìm thấy ô thì dừng lại

        let currentVal = parseInt(qtyInput.value) || 1;
        
        // Sửa lỗi: Nếu DB chưa có tồn kho (max bị rỗng) thì cho mặc định max là 9999 để không bị liệt nút
        let maxStr = qtyInput.getAttribute('max');
        let maxVal = (maxStr && maxStr !== "") ? parseInt(maxStr) : 9999; 
        
        let newVal = currentVal + change;
        
        // Nếu số lượng hợp lệ thì mới cập nhật
        if (newVal >= 1 && newVal <= maxVal) {
            qtyInput.value = newVal;
        }
    }
</script>