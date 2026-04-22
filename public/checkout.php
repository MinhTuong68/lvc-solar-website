<div class="breadcrumb-bar">
    <div class="container">
        <div class="breadcrumb">
            <a href="?page=home">Trang chủ</a><span class="sep">/</span>
            <a href="?page=products">Gỏi hàng</a><span class="sep">/</span>
            <span class="current">Thanh toán</span>
        </div>
    </div>
</div><br>

<div class="container">
    <h1 class="page-heading"><i class="fa-solid fa-bag-shopping"></i> Xác nhận đơn hàng</h1>

    <div id="checkout-empty" class="empty-cart" style="display: none;">
        <i class="fa-solid fa-cart-shopping"></i>
        <h3>Giỏ hàng của bạn đang trống</h3>
        <p style="color: var(--gray-600);">Hãy chọn cho mình những thiết bị Solar tốt nhất nhé!</p><br>
        <a href="?page=products" class="btn btn-primary">Tiếp tục mua sắm</a>
    </div>

    <div id="checkout-form-wrap" class="checkout-grid" style="display: none;">
        <div class="checkout-billing">
            <div class="checkout-card"><br>
                <h2 class="checkout-card-title">Thông tin giao hàng</h2><br>
                <form id="form-checkout">
                    <div class="form-group">
                        <label class="form-label required">Họ và tên</label>
                        <input type="text" id="c_name" class="form-control" placeholder="Nhập họ tên đầy đủ..." required>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required">Số điện thoại</label>
                            <input type="tel" id="c_phone" class="form-control" placeholder="09xxxxxxx..." required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Email (Tùy chọn)</label>
                            <input type="email" id="c_email" class="form-control" placeholder="nguyenvana@gmail.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Địa chỉ nhận hàng</label>
                        <textarea id="c_address" class="form-control" rows="2" placeholder="Số nhà, đường, phường/xã, tỉnh/thành..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ghi chú thêm</label>
                        <textarea id="c_note" class="form-control" rows="2" placeholder="Giao giờ hành chính, gọi trước khi giao..."></textarea>
                    </div>
                </form>
            </div><br>

            <div class="checkout-card">
                <h2 class="checkout-card-title">Phương thức thanh toán</h2><br>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cod" checked>
                        <div class="payment-option-body">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <div>
                                <strong>Thanh toán khi nhận hàng (COD)</strong>
                                <small>Kiểm tra hàng rồi mới trả tiền mặt</small>
                            </div>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="bank_transfer">
                        <div class="payment-option-body">
                            <i class="fa-solid fa-building-columns"></i>
                            <div>
                                <strong>Chuyển khoản ngân hàng</strong>
                                <small>Quét mã QR tự động xác nhận đơn (Khuyên dùng)</small>
                            </div>
                        </div>
                    </label>
                </div><br>
                <div id="bank-info" class="bank-info-box" style="display: none">
                    <div class="bank-info-row"><span>Ngân hàng:</span><strong>Vietcombank</strong></div>
                    <div class="bank-info-row"><span>Số tài khoản:</span><strong>1234567890</strong></div>
                    <div class="bank-info-row"><span>Chủ tài khoản:</span><strong>CÔNG TY LVC SOLAR</strong></div>
                    <div class="bank-info-row"><span>Nội dung CK:</span><strong>LVC [Số điện thoại]</strong></div>
                </div>
            </div>
        </div>

         <!-- Order summary -->
        <div class="checkout-summary">
            <div class="checkout-card">
                <h2 class="checkout-card-title">Đơn hàng của bạn</h2>
                <div id="checkout-items-list"></div>
                <div class="order-totals">
                    <div class="total-row"><span>Tạm tính:</span><strong id="checkout-subtotal">0 ₫</strong></div>
                    <div class="total-row"><span>Phí vận chuyển:</span><strong class="text-success">Miễn phí</strong></div>
                    <div class="total-row total-grand"><span>Tổng cộng:</span><strong id="checkout-total" class="text-primary">0 ₫</strong></div>
                </div>
                <div id="order-error" class="alert alert-danger" style="display: none"></div>
                <div id="order-success" class="alert alert-success" style="display: none"></div><br>
                <button id="btn-submit-order" class="btn btn-primary btn-block btn-lg">
                    <i class="fa-solid fa-lock"></i> Đặt hàng ngay
                </button>
                <a href="?page=cart" class="btn btn-ghost btn-block" style="margin-top:10px">← Quay lại giỏ hàng</a>
            </div>
        </div>
    </div>
</div><br><br>
<script src="assets/js/checkout.js"></script>