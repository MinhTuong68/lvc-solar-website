<div class="breadcrumb-bar"><div class="container"><div class="breadcrumb">
  <a href="/">Trang chủ</a><span class="sep">/</span><span class="current">Liên hệ</span>
</div></div></div>

<div class="page-header">
  <div class="container">
    <h1>Liên Hệ Với Chúng Tôi</h1>
    <p>Khảo sát miễn phí — Tư vấn 24/7 — Phản hồi trong 30 phút</p>
  </div>
</div>

<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <span class="section-label">Hỗ trợ khách hàng</span>
                <h2>Tư Vấn Miễn Phí<br>Mọi Lúc, Mọi Nơi</h2>
                <p style="margin-bottom:2rem">Đội ngũ kỹ thuật viên và tư vấn viên của LVC Solar luôn sẵn sàng hỗ trợ bạn. Liên hệ ngay để được tư vấn giải pháp điện mặt trời phù hợp nhất.</p>
                <div class="contact-item">
                    <div class="icon"><i class="fa-solid fa-location-dot"></i></div>
                    <div><h4>Trụ sở chính</h4><p><?php echo nl2br($settings['address']); ?></p></div>
                </div>
                <div class="contact-item">
                    <div class="icon"><i class="fa-solid fa-phone"></i></div>
                    <div><h4>Hotline kinh doanh</h4><p>0941 111 152</p></div>
                </div>
                <div class="contact-item">
                    <div class="icon"><i class="fa-solid fa-wrench"></i></div>
                    <div><h4>Hotline kỹ thuật</h4><p><?php echo $settings['hotline'] ?> (Hỗ trợ 24/7)</p></div>
                </div>
                 <div class="contact-item">
                    <div class="icon"><i class="fa-solid fa-envelope"></i></div>
                    <div><h4>Email</h4><p><?php echo $settings['email'] ?></p></div>
                </div>
                <div class="contact-item">
                    <div class="icon"><i class="fa-solid fa-clock"></i></div>
                    <div><h4>Giờ làm việc</h4><p>Thứ 2 – Thứ 7: 7:00 – 17:30<br><span style="font-size:.85rem;color:var(--gray-400)">Chủ nhật: Chỉ hỗ trợ khẩn cấp</span></p></div>
                </div>
                <div class="contact-item">
                    <div class="icon"><i class="fa-solid fa-qrcode"></i></div>
                    <div><h4>Mã số thuế doanh nghiệp</h4><p>1900692997</p></div>
                </div>

                 <!-- Google Map embed placeholder -->
                <div style="margin-top:24px;border-radius:var(--radius);overflow:hidden;border:1px solid var(--gray-200)">
                    <iframe src="https://maps.google.com/maps?q=9.293128,105.7411339&z=18&output=embed" width="100%" height="220" style="border:0" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>

            <div class="contact-form" id="contact-form">
                <h3>Gửi Tin Nhắn Cho Chúng Tôi</h3>
                <div id="flash-msg"></div>
                <form action="actions/add-contact.php" method="POST" onsubmit="showLoading('Đang gửi yêu cầu, vui lòng đợi...');">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Họ và tên <span>*</span></label>
                            <input type="text" name="fullname" class="form-control" placeholder="Nguyễn Văn A" required>
                        </div>
                        <div class="form-group">
                            <label>Số điện thoại <span>*</span></label>
                            <input type="tel" name="phone" class="form-control" placeholder="0912 345 678" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com">
                    </div>
                    <div class="form-group">
                        <label>Chủ đề</label>
                        <select name="subject" class="form-control">
                        <option>Tư vấn lắp đặt mới</option>
                        <option>Vệ sinh / Bảo trì</option>
                        <option>Sự cố kỹ thuật</option>
                        <option>Báo giá vật tư</option>
                        <option>Hợp tác đại lý</option>
                        <option>Khác</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nội dung <span>*</span></label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Mô tả chi tiết nhu cầu của bạn để chúng tôi có thể tư vấn tốt nhất..." required></textarea>
                    </div>
                    <button type="submit" name="btn_send_contact" class="btn btn-primary btn-block btn-lg">
                        <i class="fa-solid fa-paper-plane"></i> Gửi tin nhắn
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>