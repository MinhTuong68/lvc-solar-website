<?php
    include('../classes/order.php');
    $orderObj = new Order($conn);

    $search_phone = '';
    $orders = [];
    $security_error = false;
    $authorized_phones = [];

    if (isset($_SESSION['new_orders'])) {
        unset($_SESSION['new_orders']); // Xóa biến session này đi thì cái số trên header sẽ tự biến mất
    }

    $authorized_phones = $_SESSION['authorized_phones'] ?? [];

    if (count($authorized_phones) > 0) {
        foreach ($authorized_phones as $phone) {
            $phone_orders = $orderObj->getOrdersByPhone($phone);
            $orders = array_merge($orders, $phone_orders);
        }
        // Sắp xếp gộp đơn mới nhất lên đầu
        usort($orders, function($a, $b) {
            return $b['id'] <=> $a['id'];
        });
        
        $search_phone = end($authorized_phones); // Điền SĐT mới nhất lên ô tìm kiếm
    }

    // Nếu khách hàng nhập số điện thoại và bấm Tra cứu
    if (isset($_POST['phone']) && !empty($_POST['phone'])) {
        $search_input = trim($_POST['phone']);
        $search_phone = $search_input;

        if (count($authorized_phones) == 0) {
            // Máy lạ hoắc chưa mua bao giờ mà đi dò số -> Block
            $security_error = true;
            $orders = []; 
        }
        else{
            if (in_array($search_input, $authorized_phones)) {
                // Đúng số của mình -> Lọc ra đúng đơn của số đó
                $orders = $orderObj->getOrdersByPhone($search_input);
                $security_error = false;
            }
            else{
                $filtered_orders = [];
                foreach ($orders as $o) {
                    if (stripos($o['order_code'], $search_input) !== false) {
                        $filtered_orders[] = $o;
                    }
                }
                if (count($filtered_orders) > 0) {
                    // Tìm thấy mã đơn trong danh sách của mình -> Show ra
                    $orders = $filtered_orders;
                    $security_error = false;
                } else {
                    // Nhập tào lao (Số người khác hoặc mã đơn sai) -> Block
                    $security_error = true;
                    $orders = []; 
                }
            }
        }
    }
?>
<div class="breadcrumb-bar">
    <div class="container">
        <div class="breadcrumb">
            <a href="?page=home">Trang chủ</a><span class="sep">/</span><span class="current">Lịch sử đơn hàng</span>
        </div>
    </div>
</div>

<div class="order-history-wrapper">
    <div class="container">
        <form id="form-tra-cuu" method="POST" action="" class="order-lookup-card">
            <input type="hidden" name="page" value="order_history"> 
            <i class="fa-solid fa-receipt" style="font-size: 24px; color: var(--navy);"></i>
            <input type="text" id="input-phone" name="phone" placeholder="Nhập số điện thoại đã đặt hàng của bạn...">
            <button type="submit" class="btn btn-navy" onclick="sendOTP(event)">Tra cứu</button>
        </form><br><br>
        
        <?php
            if ($security_error == true){
                ?>
                    <div style="text-align:center; padding: 50px 20px; background:#fff; border-radius:15px; border: 1px dashed #ef4444;">
                        <i class="fa-solid fa-shield-halved" style="font-size:50px; color:#ef4444; margin-bottom:20px;"></i>
                        <h3 style="color:var(--navy); font-size:1.2rem; margin-bottom:10px;">Truy cập bị từ chối</h3>
                        <p style="color:var(--gray-400);">Dữ liệu không khớp với phiên giao dịch hiện tại, hoặc bạn đang tìm đơn không thuộc về mình.</p>
                    </div>
                <?php
            }
            elseif (count($authorized_phones) > 0){
                if (count($orders) > 0){
                    foreach ($orders as $order){
                        $details = $orderObj->getOrderDetails($order['id']);

                        $status_class = ''; $status_icon = ''; $status_text = '';

                        switch ($order['status']){
                            case 'new':
                                $status_class = 'status-new';
                                $status_icon = '<i class="fa-solid fa-spinner fa-spin"></i>';
                                $status_text = 'Đang chờ xác nhận';
                                break;
                            case 'cancelled':
                                $status_class = 'status-cancelled';
                                $status_icon = '<i class="fa-solid fa-circle-xmark"></i>';
                                $status_text = 'Đã hủy';
                                break;
                            default: // confirmed, shipping, done
                                $status_class = 'status-confirmed';
                                $status_icon = '<i class="fa-solid fa-circle-check"></i>';
                                $status_text = 'Đã xác nhận & Đang xử lý';
                                break;
                        }
                        ?>
                            <div class="order-card">
                                 <div class="order-card-header">
                                    <div>
                                        <div class="order-id-label">Mã đơn: #<?= $order['order_code'] ?></div>
                                        <span class="order-date-label">Đặt lúc: <?= date('H:i - d/m/Y', strtotime($order['created_at'])) ?></span>
                                    </div>
                                    <div class="status-badge <?= $status_class ?>">
                                        <?= $status_icon ?> <?= $status_text ?>
                                    </div>
                                </div>

                                <div class="order-card-body">
                                    <?php
                                        foreach ($details as $item){
                                            ?>
                                                <div class="order-product-item">
                                                    <img src="<?php echo ROOT_URL ?>uploads/products/images/<?= $item['product_image'] ?>" class="order-product-img" onerror="this.src='../uploads/products/images/default.jpg'">
                                                    <div class="order-product-info">
                                                        <div class="order-product-name"><?= $item['product_name'] ?></div>
                                                        <div class="order-product-meta">Số lượng: <?= sprintf("%02d", $item['quantity']) ?> | Đơn giá: <?= number_format($item['price'], 0, ',', '.') ?>đ</div>
                                                    </div>
                                                    <div class="order-product-price"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ</div>
                                                </div>
                                            <?php
                                        }
                                    ?>
                                </div>

                                <div class="order-card-footer">
                                    <div class="order-total-group">
                                        <span class="order-total-text">
                                            Tổng thanh toán: 
                                            <?php 
                                                if($order['payment_status'] == 'paid'){
                                                    ?>
                                                        <span style="color:var(--green); font-size: 0.8rem;">(Đã thanh toán)</span>
                                                    <?php  
                                                }
                                                else{
                                                    ?>
                                                        <span style="color: #ff4757; font-size: 0.8rem;">(Chưa thanh toán)</span>
                                                    <?php
                                                }
                                            ?>
                                        </span>
                                        <span class="order-total-amount"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</span>
                                    </div>

                                    <div class="order-actions">
                                        <?php
                                            if ($order['status'] == 'new'){
                                                ?>
                                                    <form action="actions/cancel-order.php" method="POST" style="display: inline-block; margin: 0;">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                        <button type="button" class="btn-order btn-cancel" 
                                                            onclick="const formHuyDon = this.closest('form'); openModal(
                                                                    'Xác nhận hủy đơn?',
                                                                    'Bạn có chắc chắn muốn hủy đơn hàng này không?',
                                                                    'fa-solid fa-circle-exclamation',
                                                                    'Hủy đơn',
                                                                    function () {
                                                                        formHuyDon.submit();
                                                                    }
                                                            )">
                                                            <i class="fa-solid fa-xmark"></i> Hủy đơn
                                                        </button>
                                                    </form>
                                    
                                                    <a href="?page=edit_order&id=<?= $order['id'] ?>" class="btn-order btn-edit"><i class="fa-solid fa-pen-to-square"></i> Sửa đơn</a>
                                                    <?php
                                                        if ($order['payment_status'] == 'unpaid'){
                                                            ?>
                                                                <a href="?page=pay_online&id=<?= $order['id'] ?>" class="btn-order btn-pay" style="display: none;">
                                                                    <i class="fa-solid fa-credit-card"></i> Thanh toán Online
                                                                </a>
                                                            <?php
                                                        }            
                                                    ?>
                                                <?php
                                            }else{
                                                ?>
                                                    <a href="?page=detail_order&id=<?= $order['id'] ?>" class="btn-order btn-detail">
                                                        <i class="fa-solid fa-eye"></i> Xem chi tiết
                                                    </a>
                                                <?php
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php
                    }
                }
                else {
                    // CÓ SESSION nhưng do khách gõ tìm MÃ ĐƠN HÀNG bị sai nên trả về 0 kết quả
                    ?>
                        <div style="text-align:center; padding: 50px 20px; background:#fff; border-radius:15px; border: 1px dashed var(--gray-200);">
                            <i class="fa-solid fa-box-open" style="font-size:50px; color:var(--gray-200); margin-bottom:20px;"></i>
                            <h3 style="color:var(--navy); font-size:1.2rem; margin-bottom:10px;">Không tìm thấy đơn hàng</h3>
                            <p style="color:var(--gray-400);">Không có đơn hàng nào khớp với <b><?= htmlspecialchars($search_phone) ?></b> trong danh sách của bạn.</p>
                        </div>
                    <?php
                }
            }
            else {
                // TRƯỜNG HỢP MẶC ĐỊNH: Vừa mở trình duyệt lên, KHÔNG có Session
                ?>
                    <div style="text-align:center; padding: 40px 20px; color: var(--gray-400);">
                        <p><i class="fa-solid fa-arrow-up" style="margin-bottom:15px; font-size:24px; display:block;"></i></p>
                        <p>Vui lòng tiến hành đặt hàng để xem lịch sử mua hàng của bạn.</p>
                    </div>
                <?php
            }      
        ?>
    </div>
</div>

<div id="modal-otp" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999;">
    <div style="background: #fff; width: 400px; margin: 100px auto; padding: 30px; border-radius: 10px; text-align: center;">
        <h3 style="color: var(--navy); margin-bottom: 10px;">Xác thực tài khoản</h3>
        <p id="otp-message" style="font-size: 14px; color: #64748b; margin-bottom: 20px;">Mã xác nhận đã được gửi về email của bạn.</p>
        
        <input type="hidden" id="hidden-verify-phone" value="">
        <input type="text" id="input-otp" placeholder="Nhập mã 6 số..." style="width: 100%; padding: 10px; font-size: 18px; text-align: center; letter-spacing: 5px; margin-bottom: 20px; border: 1px solid #cbd5e1; border-radius: 5px;">
        
        <button onclick="verifyOTP()" style="background: var(--amber); color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-weight: bold;">XÁC NHẬN MÃ</button>
        <button onclick="document.getElementById('modal-otp').style.display='none'" style="background: none; color: #94a3b8; border: none; margin-top: 15px; cursor: pointer; text-decoration: underline;">Hủy bỏ</button>
    </div>
</div>

<script>
function sendOTP(event) {
    event.preventDefault(); // Chặn load trang khi bấm Tra cứu
    let phone = document.getElementById('input-phone').value;

    if (!phone) {
        alert("Vui lòng nhập số điện thoại!");
        return;
    }
    showLoading('Đang gửi mã OTP về email của bạn...');

    // Gọi AJAX gửi OTP
    fetch('ajax/send_otp.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'phone=' + phone
    })
    .then(res => res.json())
    .then(data => {
        hideLoading();
        if(data.status === 'success') {
            document.getElementById('hidden-verify-phone').value = phone;
            document.getElementById('otp-message').innerHTML = "Mã xác nhận đã được gửi về email: <br><b>" + data.email_hint + "</b>";
            document.getElementById('modal-otp').style.display = 'block';
        } else {
            alert(data.message); // Báo lỗi nếu SĐT chưa từng mua hàng
        }
    }).catch(error => {
        hideLoading();
        alert("Lỗi kết nối, vui lòng thử lại!");
    });
}

function verifyOTP() {
    let otp = document.getElementById('input-otp').value;
    let phone = document.getElementById('hidden-verify-phone').value;

    if(!otp) { alert("Vui lòng nhập mã OTP"); return; }

    // Gọi AJAX kiểm tra OTP
    fetch('ajax/verify_otp.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'otp=' + otp + '&phone=' + phone
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            // Đúng mã -> Tự động submit cái form tra cứu ban đầu để tải đơn hàng ra
            document.getElementById('form-tra-cuu').submit();
        } else {
            alert('Mã OTP không chính xác hoặc đã hết hạn!');
        }
    });
}
</script>