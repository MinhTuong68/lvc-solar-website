<?php
    include("../classes/order.php");
    $order_obj = new Order($conn);
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $order = $order_obj->getOrderByID($id);
    if (!$order) {
        $_SESSION['toast_message'] = "Lỗi truy cập, vui lòng thử lại!";
        $_SESSION['toast_type'] = 'error';  
        header("Location: ?page=order-detail"); 
        exit();
    }
    $orderDetails = $order_obj->getOrderDetails($id);
    $msg = "";
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
        $res = $order_obj->updateOrder($id, $_POST['status'], $_POST['payment_status'], $_POST['note']);
        if ($res) {
            $_SESSION['toast_message'] = "Cập nhập đơn hàng thành công";
            $_SESSION['toast_type'] = 'success'; 
            header("Location: ?page=order-detail&id=$id");
            exit();
        }
    }

    // --- LẤY DANH MỤC VÀ SẢN PHẨM ĐỂ HIỂN THỊ LÊN MENU GỌI MÓN ---
    $categories = [];
    $cat_query = $conn->query("SELECT * FROM tbl_categories WHERE status = 1");
    if($cat_query){ 
        while($r = $cat_query->fetch_assoc()){ 
            $categories[] = $r; 
        } 
    }

    $products = [];
    $prod_query = $conn->query("SELECT * FROM tbl_products WHERE status = 1");
    if($prod_query){ 
        while($r = $prod_query->fetch_assoc()){ 
            $products[] = $r; 
        } 
    }

    if(isset($_POST['add_new_product'])){
        $order_id_to_add = (int)$_POST['current_order_id'];
        $product_id = (int)$_POST['new_product_id'];
        $qty = (int)$_POST['new_quantity'];

        $result = $order_obj->addProductToOrder($order_id_to_add, $product_id, $qty);

        $_SESSION['toast_message'] = $result['msg'];

        // 2. Kiểm tra trạng thái để hiển thị màu (Thành công = Xanh, Lỗi = Đỏ)
        if ($result['status'] == true) {
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_type'] = 'error';
        }
        
        header("Location: index.php?page=order-detail&id=$id");
        exit();
    }
?>

<div class="wrapper">
    <div class="cs-page-wrapper">
        <div class="cs-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1><i class="fa-solid fa-receipt" style="color: var(--amber);"></i> Chi tiết Đơn hàng</h1>
                <span style="color: #64748b; font-size: 14px;">Mã đơn: <strong><?= $order['order_code'] ?></strong></span>
            </div>
            <a href="index.php?page=manage-order" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <button type="button" class="btn-primary" id="toggleAddProduct"><i class="fa-solid fa-plus"></i>Thêm sản phẩm</button>
        <div class="card-wrapper" id="cardwrapper"> 
            <div class="card-content">
                <h3 style="margin-bottom: 15px; color: var(--navy); display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-cart-shopping"></i> Menu Thêm Sản Phẩm
                </h3>
                <div class="pos-categories">
                    <button type="button" class="cat-btn active" onclick="filterCategory('all')">Tất cả</button>
                    
                    <?php 
                    foreach($categories as $cat){
                            ?>
                                <button type="button" class="cat-btn" onclick="filterCategory(<?= $cat['id'] ?>)">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </button>
                            <?php
                        } 
                    ?>
                </div><br>

                <div class="product-menu-box">
                    <?php
                        foreach($products as $sp){
                            ?>
                                <div class="product-menu-item">
                                    <div class="card-product-img">
                                        <?php
                                            if($sp['image']==""){
                                                echo "<div class='error'>No image</div>";
                                            }
                                            else{
                                                ?>
                                                    <img src="<?php echo ROOT_URL?>/uploads/products/images/<?= $sp['image'] ?>" alt="">
                                                <?php
                                            }
                                        ?>
                                    </div>
                                </div>

                                <div class="card-desc">
                                    <h4><?php echo $sp['name'] ?></h4>
                                    <div class="card-item-price">
                                        <?= number_format($sp['price'], 0, ',', '.') ?> VNĐ
                                    </div>
                                    <div class="card-item-power-capacity">
                                        <?php
                                            if (!empty($sp['power_capacity'])) {
                                                // Nếu sản phẩm CÓ thông số công suất
                                                echo htmlspecialchars($sp['power_capacity']);
                                            } else {
                                                // Nếu KHÔNG CÓ công suất, thì hiện số lượng kho
                                                echo 'Có sẵn: ' . htmlspecialchars($sp['stock']). ' sản phẩm';
                                            }
                                        ?>
                                    </div>
                                    <form action="" method="POST" style="display: flex; gap: 10px; align-items: center; margin-top: 10px;">
                                        <input type="hidden" name="current_order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="new_product_id" value="<?= $sp['id'] ?>">
                                        
                                        <input type="number" name="new_quantity" value="1" min="1" max="<?= $sp['stock'] ?>" class="card-input">
                                        
                                        <button type="submit" name="add_new_product" class="btn-add-card">
                                            Thêm món
                                        </button>
                                    </form>
                                </div>
                            <?php
                        }
                    ?>
                </div>
            </div>
        </div><br><br>

        <div class="od-container">
            <div class="od-main">
                <div class="od-card">
                    <div class="od-card-header">
                        <h3 class="od-card-title">Sản phẩm đã đặt</h3>
                        <span class="cs-status-badge" style="background: #f1f5f9; color: #475569;"><?= count($orderDetails) ?> món</span>
                    </div>
                    <div class="od-card-body">
                        <table class="od-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th style="text-align: center;">Số lượng</th>
                                    <th style="text-align: right;">Đơn giá</th>
                                    <th style="text-align: right;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderDetails as $item): ?>
                                <tr>
                                    <td>
                                        <div class="od-prod-info">
                                            <div class="od-prod-name word-break"><?= htmlspecialchars($item['product_name']) ?></div>
                                        </div>
                                    </td>
                                    <td style="text-align: center; font-weight: 700;">x<?= $item['quantity'] ?></td>
                                    <td style="text-align: right;"><?= number_format($item['price'], 0, ',', '.') ?>đ</td>
                                    <td style="text-align: right; font-weight: 700; color: var(--navy);">
                                        <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table><br>

                        <div class="od-summary">   
                            <div>
                                <div class="summary-row">
                                    <span>Tạm tính:</span>
                                    <span><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</span>
                                </div>
                                <div class="summary-row">
                                    <span>Phí vận chuyển:</span>
                                    <span style="color: #059669;">Miễn phí</span>
                                </div>
                            </div>
                            <div>
                                <div class="summary-row total">
                                    <span>TỔNG CỘNG:</span>
                                    <span><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><br>

                <div class="od-card" style="padding: 10px;">
                    <div class="od-card-header"><h3 class="od-card-title">Thông tin khách hàng</h3></div>
                    <div class="od-card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Người nhận</span>
                                <div class="info-value"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($order['customer_name']) ?></div>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Số điện thoại</span>
                                <div class="info-value"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($order['customer_phone']) ?></div>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <div class="info-value"><i class="fa-solid fa-envelope"></i> <?= !empty($order['customer_email']) ? htmlspecialchars($order['customer_email']) : 'Không có' ?></div>
                            </div>
                        </div><br>
                        <div class="info-item">
                            <span class="info-label">Địa chỉ giao hàng</span>
                            <div class="info-value"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($order['customer_address']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="od-sidebar">
                <form action="" method="POST">
                    <div class="od-card" style="border-top: 4px solid #2ed573;">
                        <div class="od-card-header"><h3 class="od-card-title">Xử lý Đơn hàng</h3></div>
                        <div class="od-card-body">
                            <div class="form-group-add" style="margin-bottom: 20px;">
                                <label class="form-label-add">Trạng thái đơn hàng</label>
                                <select name="status" class="form-control" style="font-weight: 700;">
                                    <option value="new" <?= $order['status'] == 'new' ? 'selected' : '' ?>>Mới nhận</option>
                                    <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                    <option value="shipping" <?= $order['status'] == 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                                    <option value="done" <?= $order['status'] == 'done' ? 'selected' : '' ?>>Hoàn thành</option>
                                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                </select>
                            </div>

                            <div class="form-group-add" style="margin-bottom: 20px;">
                                <label class="form-label-add">Thanh toán</label>
                                <select name="payment_status" class="form-control">
                                    <option value="unpaid" <?= $order['payment_status'] == 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán</option>
                                    <option value="paid" <?= $order['payment_status'] == 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                                </select>
                                <small style="display: block; margin-top: 5px; color: #64748b;">
                                    Hình thức: <strong><?= strtoupper($order['payment_method']) ?></strong>
                                </small>
                            </div>

                            <div class="form-group-add">
                                <label class="form-label-add">Ghi chú của khách/admin</label>
                                <textarea name="note" class="form-control" rows="4"><?= htmlspecialchars($order['note'] ?? '') ?></textarea>
                            </div>
                            <a href="index.php?page=print-bill&id=<?= $order['id'] ?>" class="btn-print">
                                <i class="fa-solid fa-file-invoice"></i> Xuất hóa đơn
                            </a>
                            <button type="submit" name="update_order" class="cs-btn-filter" style="width: 100%; margin-top: 20px; padding: 12px; border-radius: 8px;">
                                <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                            </button>
                        </div>
                    </div>
                </form><br>

                <div class="od-card">
                    <div class="od-card-header"><h3 class="od-card-title">Thời gian</h3></div>
                    <div class="od-card-body">
                        <div class="info-item">
                            <span class="info-label">Ngày đặt hàng</span>
                            <div class="info-value"><i class="fa-regular fa-calendar-plus"></i> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Cập nhật cuối</span>
                            <div class="info-value"><i class="fa-solid fa-clock-rotate-left"></i> <?= date('d/m/Y H:i', strtotime($order['updated_at'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/addproduct.js"></script>

<script>
    // BỎ DOMContentLoaded ĐI, GỌI TRỰC TIẾP LUÔN NHƯ THẾ NÀY:
    if (typeof setupTogglePanel === 'function') {
        setupTogglePanel('toggleAddProduct', 'cardwrapper');
    } else {
        alert("Lỗi: Không load được file addproduct.js, hãy kiểm tra lại đường dẫn thẻ src!");
    }
</script>