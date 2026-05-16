<?php
    include("../classes/order.php");
    $orderObj = new Order($conn);

    // 1. Lấy ID đơn hàng từ URL
    if (!isset($_GET['id'])) {
        header("Location: ?page=order_history");
        exit;
    }

    $id = (int)$_GET['id'];
    $order = $orderObj->getOrderByID($id);

    // 2. Kiểm tra bảo mật: Khách chỉ xem được đơn của chính mình (dựa trên Session điện thoại)
    $authorized_phones = isset($_SESSION['authorized_phones']) ? $_SESSION['authorized_phones'] : [];

    if (!$order || !in_array($order['customer_phone'], $authorized_phones)) {
        $_SESSION['toast_message'] = "Bạn không có quyền xem đơn hàng này!";
        $_SESSION['toast_type'] = "error";
        header("Location: ?page=order_history");
        exit;
    }

    // 3. Lấy danh sách sản phẩm trong đơn
    $details = $orderObj->getOrderDetails($id);

    // 4. Hàm bổ trợ để map trạng thái sang class CSS và tên hiển thị
    function getStatusInfo($status) {
        $data = [
            'new'       => ['label' => 'Chờ xác nhận', 'class' => 'status-new', 'step' => 1],
            'confirmed' => ['label' => 'Đã xác nhận', 'class' => 'status-confirmed', 'step' => 2],
            'shipping'  => ['label' => 'Đang giao hàng', 'class' => 'status-shipping', 'step' => 3],
            'done' => ['label' => 'Đã hoàn thành', 'class' => 'status-completed', 'step' => 4],
            'cancelled'  => ['label' => 'Đã hủy', 'class' => 'status-canceled', 'step' => 0]
        ];
        return isset($data[$status]) ? $data[$status] : ['label' => 'Không rõ', 'class' => '', 'step' => 0];
    }

    $statusInfo = getStatusInfo($order['status']);
?>
<div class="breadcrumb-bar">
    <div class="container">
        <div class="breadcrumb">
            <a href="?page=home">Trang chủ</a>
            <span class="sep">/</span>
            <a href="?page=order_history">Lịch sử đơn hàng</a>
            <span class="sep">/</span>
            <span class="current">Chi tiết đơn hàng #LVC12345</span>
        </div>
    </div>
</div>

<div class="order-detail-wrapper">
    <div class="container">
        <div class="order-header-card">
            <div class="oh-left">
                <h1>Đơn hàng: #<?= $order['order_code'] ?></h1>
                <span class="order-date">Ngày đặt: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="oh-right">
                <span class="order-status-badge <?php echo $statusInfo['class']; ?>">
                    <?php echo $statusInfo['label']; ?>
                </span>
            </div>
        </div>

       <div class="order-timeline-card">
            <div class="timeline-steps">
                <div class="step <?php if ($statusInfo['step'] >= 1) { echo 'active'; } else { echo ''; } ?>">
                    <div class="step-icon"><i class="fa-solid fa-file-invoice"></i></div>
                    <span>Đã đặt đơn</span>
                </div>
                
                <div class="step <?php if ($statusInfo['step'] >= 2) { echo 'active'; } else { echo ''; } ?>">
                    <div class="step-icon"><i class="fa-solid fa-check-double"></i></div>
                    <span>Đã xác nhận</span>
                </div>
                
                <div class="step <?php if ($statusInfo['step'] >= 3) { echo 'active'; } else { echo ''; } ?>">
                    <div class="step-icon"><i class="fa-solid fa-truck-fast"></i></div>
                    <span>Đang giao</span>
                </div>
                
                <div class="step <?php if ($statusInfo['step'] >= 4) { echo 'active'; } else { echo ''; } ?>">
                    <div class="step-icon"><i class="fa-solid fa-house-circle-check"></i></div>
                    <span>Đã giao</span>
                </div>
            </div>
        </div>

        <div class="order-info-grid">
            <div class="info-card">
                <h3><i class="fa-solid fa-location-dot"></i> Địa chỉ nhận hàng</h3>
                <div class="info-content">
                    <strong><?= $order['customer_name'] ?></strong>
                    <p><?= $order['customer_phone'] ?></p>
                    <p><?= $order['customer_address'] ?></p>
                </div>
            </div>
            <div class="info-card">
                <h3><i class="fa-solid fa-credit-card"></i> Phương thức thanh toán</h3>
                <div class="info-content">
                    <p>Thanh toán khi nhận hàng (COD)</p>
                    <span class="payment-status <?= ($order['status'] == 'done') ? 'paid' : 'unpaid' ?>">
                        <?= ($order['status'] == 'done') ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
                    </span>
                </div>
            </div>
            <div class="info-card">
                <h3><i class="fa-solid fa-comment-dots"></i> Ghi chú đơn hàng</h3>
                <div class="info-content">
                    <p><?= !empty($order['note']) ? $order['note'] : 'Không có ghi chú' ?></p>
                </div>
            </div>
        </div>

        <div class="order-items-card">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th class="text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach ($details as $item){
                            ?>
                                <tr>
                                    <td>
                                        <div class="item-product">
                                            <img src="<?php echo ROOT_URL ?>/uploads/products/images/<?= $item['product_image'] ?>" onerror="this.src='../uploads/products/images/default.jpg'">
                                            <div class="item-info">
                                                <span class="item-name"><?= $item['product_name'] ?></span>
                                                <span class="item-sku">Mã SP: <?= $item['product_id'] ?></span></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= number_format($item['price'], 0, ',', '.') ?>₫</td>
                                    <td>x<?= $item['quantity'] ?></td>
                                    <td class="text-right"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>₫</td>
                                </tr>
                            <?php
                        }
                    ?>
                </tbody>
            </table>

            <div class="order-summary">
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <span><?= number_format($order['total_amount'], 0, ',', '.') ?></span>
                </div>
                <div class="order-summary-row">
                    <span>Phí vận chuyển:</span>
                    <span class="text-free">Miễn phí</span>
                </div>
                <div class="order-summary-row order-total-row">
                    <span>Tổng cộng:</span>
                    <span class="price"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</span>
                </div>
            </div>
        </div>

        <div class="order-footer-actions">
            <a href="?page=order_history" class="btn-back">
                <i class="fa-solid fa-chevron-left"></i> Quay lại danh sách
            </a>
            <!-- <button class="btn-print" onclick="window.print()">
                <i class="fa-solid fa-print"></i> In đơn hàng
            </button> -->
            <?php
                if($order['status'] == 'done'){
                    ?>
                        <a href="?page=print-bill&id=<?= $id ?>" class="btn-print">
                            <i class="fa-solid fa-file-invoice"></i> Xuất hóa đơn
                        </a>
                    <?php
                }
            ?>    
            <a href="?page=contact" class="btn-support">Cần hỗ trợ?</a>
        </div>
    </div>
</div>