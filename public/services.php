<!-- Breadcrumb -->
<div class="breadcrumb-bar">
  <div class="container">
    <div class="breadcrumb">
      <a href="?page=home">Trang chủ</a><span class="sep">/</span>
      <span class="current">Dịch vụ Solar</span>
    </div>
  </div>
</div>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h1>Dịch Vụ Năng Lượng Mặt Trời</h1>
    <p>Trọn gói từ tư vấn, thiết kế, thi công đến bảo trì — Uy tín · Chuyên nghiệp · Bảo hành dài hạn</p>
  </div>
</div>

<section class="srv-section">
    <div class="container">
        <!-- Lắp đặt -->
        <div id="lap-dat" class="srv-grid">
            <div class="srv-content">
                <span class="section-label">Dịch vụ 01</span>
                <h2 class="srv-title">Lắp Đặt Điện Mặt Trời Trọn Gói</h2>
                <p class="srv-desc">Chúng tôi cung cấp giải pháp lắp đặt hệ thống điện mặt trời hoàn chỉnh: hòa lưới không lưu trữ, bám tải có lưu trữ, và hệ thống độc lập cho khu vực không có điện lưới.</p>
                <ul class="srv-list">
                    <?php foreach (['Khảo sát & tư vấn miễn phí tại nhà','Thiết kế phương án tối ưu cho từng công trình','Cung cấp vật tư chính hãng, có CO/CQ','Thi công bởi thợ có chứng chỉ điện','Đăng ký hòa lưới EVN trọn gói','Bảo hành 25 năm tấm pin, 5 năm inverter'] as $item): ?>
                    <li>
                        <i class="fa-solid fa-circle-check"></i> <?= $item ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="#booking" class="btn btn-primary btn-lg"><i class="fa-solid fa-calendar-check"></i> Đặt lịch khảo sát</a>
            </div>
            <div class="srv-img-wrap">
                <img src="https://images.unsplash.com/photo-1509391366360-2e959784a276?q=80&w=800" alt="Lắp đặt solar">
            </div>
        </div>

        <hr style="border-color:var(--gray-200);margin-bottom:80px">
        <!-- Vệ sinh -->
        <div id="ve-sinh" style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;margin-bottom:80px">
            <div style="border-radius:var(--radius-lg);overflow:hidden;order:1">
                <img src="https://images.unsplash.com/photo-1466611653911-95081537e5b7?q=80&w=800" alt="Vệ sinh tấm pin" style="width:100%;aspect-ratio:4/3;object-fit:cover">
            </div>
            <div style="order:2">
                <span class="section-label">Dịch vụ 02</span>
                <h2 style="font-size:2rem;color:var(--navy);margin-bottom:1rem">Vệ Sinh Tấm Pin Chuyên Nghiệp</h2>
                <p style="color:var(--gray-600);line-height:1.8;margin-bottom:1.5rem">Bụi bẩn, rêu mốc và chất bẩn tích tụ có thể giảm hiệu suất tấm pin lên đến 30%. Vệ sinh định kỳ 1–2 lần/năm giúp duy trì hiệu suất tối đa và kéo dài tuổi thọ thiết bị.</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:2rem">
                <?php foreach ([['🚿','Máy rửa áp lực','Không trầy xước bề mặt'],['🧪','Hóa chất chuyên dụng','An toàn, không ăn mòn'],['📊','Kiểm tra hiệu suất','Đo sản lượng trước/sau'],['🛡️','Bảo hiểm thi công','An toàn tuyệt đối']] as [$ic,$title,$desc]): ?>
                <div style="background:var(--off-white);border-radius:var(--radius);padding:16px">
                    <div style="font-size:1.4rem;margin-bottom:6px"><?= $ic ?></div>
                    <div style="font-weight:700;font-size:.9rem;color:var(--navy);margin-bottom:4px"><?= $title ?></div>
                    <div style="font-size:.8rem;color:var(--gray-600)"><?= $desc ?></div>
                </div>
                <?php endforeach; ?>
                </div>
                <a href="#booking" class="btn btn-primary btn-lg"><i class="fa-solid fa-calendar-check"></i> Đặt lịch vệ sinh</a>
            </div>
        </div>

        <hr style="border-color:var(--gray-200);margin-bottom:80px">

        <!-- Bảo trì -->
        <div id="bao-tri" style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;margin-bottom:80px">
            <div>
                <span class="section-label">Dịch vụ 03</span>
                <h2 style="font-size:2rem;color:var(--navy);margin-bottom:1rem">Bảo Trì & Sửa Chữa Hệ Thống</h2>
                <p style="color:var(--gray-600);line-height:1.8;margin-bottom:1.5rem">Phát hiện và khắc phục sự cố nhanh chóng, đảm bảo hệ thống của bạn luôn vận hành ổn định. Chúng tôi hỗ trợ tất cả các thương hiệu inverter và tấm pin.</p>
                <div style="background:var(--off-white);border-radius:var(--radius);padding:20px;margin-bottom:1.5rem">
                <div style="font-weight:700;color:var(--navy);margin-bottom:12px">Bảng giá bảo trì định kỳ</div>
                <?php foreach ([['Hệ thống ≤ 5kWp','350.000₫/lần'],['Hệ thống 5–15kWp','550.000₫/lần'],['Hệ thống ≥ 15kWp','Báo giá riêng'],['Gói bảo trì năm','Ưu đãi 20%']] as [$label,$price]): ?>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--gray-200);font-size:.9rem">
                    <span style="color:var(--gray-600)"><?= $label ?></span>
                    <strong style="color:var(--green)"><?= $price ?></strong>
                </div>
                <?php endforeach; ?>
                </div>
                <a href="#booking" class="btn btn-primary btn-lg"><i class="fa-solid fa-wrench"></i> Đặt lịch bảo trì</a>
            </div>
            <div style="border-radius:var(--radius-lg);overflow:hidden">
                <img src="https://images.unsplash.com/photo-1548337138-e87d889cc369?q=80&w=800" alt="Bảo trì hệ thống solar" style="width:100%;aspect-ratio:4/3;object-fit:cover">
            </div>
        </div>

        <hr style="border-color:var(--gray-200);margin-bottom:80px">

        <!-- Nâng cấp -->
        <div id="nang-cap" style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;margin-bottom:40px">
            <div style="border-radius:var(--radius-lg);overflow:hidden">
                <img src="https://images.unsplash.com/photo-1613665813446-82a78c468a1d?q=80&w=800" alt="Nâng cấp inverter" style="width:100%;aspect-ratio:4/3;object-fit:cover">
            </div>
            <div>
                <span class="section-label">Dịch vụ 04</span>
                <h2 style="font-size:2rem;color:var(--navy);margin-bottom:1rem">Nâng Cấp Inverter & Pin Lưu Trữ</h2>
                <p style="color:var(--gray-600);line-height:1.8;margin-bottom:1.5rem">Nâng cấp từ hệ thống hòa lưới đơn thuần sang hybrid, bổ sung pin lưu trữ Lithium để sử dụng điện vào ban đêm và khi mất điện lưới.</p>
                <ul style="display:flex;flex-direction:column;gap:12px;margin-bottom:2rem">
                <?php foreach (['Đánh giá khả năng nâng cấp hệ thống hiện tại','Tư vấn chọn pin lưu trữ phù hợp công suất','Lắp đặt inverter hybrid: Deye, Solis, Growatt','Cấu hình hệ thống tự động thông minh','Đào tạo sử dụng ứng dụng giám sát từ xa'] as $item): ?>
                <li style="display:flex;align-items:center;gap:10px;font-size:.95rem;color:var(--gray-600)">
                    <i class="fa-solid fa-circle-check" style="color:var(--green)"></i> <?= $item ?>
                </li>
                <?php endforeach; ?>
                </ul>
                <a href="#booking" class="btn btn-primary btn-lg"><i class="fa-solid fa-bolt"></i> Tư vấn nâng cấp</a>
            </div>
        </div>
    </div>   
</section>

<section id="booking" class="srv-booking2">
  <div class="container">
    <div class="section-header">
        <span class="section-label" style="color:var(--amber)">Đặt lịch ngay</span>
        <h2 class="section-title" style="color:white">Đặt Lịch Khảo Sát Miễn Phí</h2>
        <p class="section-sub" style="color:rgba(255,255,255,.7);margin:0 auto">Điền thông tin bên dưới, đội kỹ thuật LVC Solar sẽ liên hệ lại trong vòng 30 phút và sắp xếp lịch khảo sát phù hợp.</p>
    </div>
    <div style="max-width:720px;margin:0 auto;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);backdrop-filter:blur(12px);border-radius:var(--radius-lg);padding:40px">
      <form id="booking-form" method="POST" action="ajax/submit_service.php">
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div class="form-group">
            <label style="color:rgba(255,255,255,.85);font-size:.85rem;font-weight:600;margin-bottom:6px;display:block">Họ và tên <span style="color:#f87171">*</span></label>
            <input type="text" name="customer_name" class="form-control" placeholder="Nguyễn Văn A" required style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2);color:white">
          </div>
          <div class="form-group">
            <label style="color:rgba(255,255,255,.85);font-size:.85rem;font-weight:600;margin-bottom:6px;display:block">Số điện thoại <span style="color:#f87171">*</span></label>
            <input type="tel" name="phone" class="form-control" placeholder="0912 345 678" required style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2);color:white">
          </div>
        </div>
        <div class="form-group" style="margin-top:12px">
          <label style="color:rgba(255,255,255,.85);font-size:.85rem;font-weight:600;margin-bottom:6px;display:block">Địa chỉ lắp đặt</label>
          <input type="text" name="address" class="form-control" placeholder="Số nhà, đường, phường/xã, tỉnh/thành" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2);color:white">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px">
          <div class="form-group">
            <label style="color:rgba(255,255,255,.85);font-size:.85rem;font-weight:600;margin-bottom:6px;display:block">Loại dịch vụ</label>
            <select name="service_type" class="form-control" style="background:rgba(15,23,42,.8);border-color:rgba(255,255,255,.2);color:white">
              <option value="lap_dat">Lắp đặt mới</option>
              <option value="ve_sinh">Vệ sinh tấm pin</option>
              <option value="bao_tri">Bảo trì hệ thống</option>
              <option value="nang_cap">Nâng cấp hệ thống</option>
            </select>
          </div>
          <div class="form-group">
            <label style="color:rgba(255,255,255,.85);font-size:.85rem;font-weight:600;margin-bottom:6px;display:block">Tiền điện TB/tháng</label>
            <input type="text" name="electricity_bill" class="form-control" placeholder="VD: 1.500.000₫" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2);color:white">
          </div>
        </div>
        <div class="form-group" style="margin-top:12px">
          <label style="color:rgba(255,255,255,.85);font-size:.85rem;font-weight:600;margin-bottom:6px;display:block">Ngày muốn khảo sát</label>
          <input type="date" name="booking_date" class="form-control" min="<?= date('Y-m-d') ?>" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2);color:white">
        </div>
        <div class="form-group" style="margin-top:12px">
          <label style="color:rgba(255,255,255,.85);font-size:.85rem;font-weight:600;margin-bottom:6px;display:block">Mô tả thêm (vấn đề, yêu cầu đặc biệt...)</label>
          <textarea name="issue_description" class="form-control" rows="3" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2);color:white;resize:none" placeholder="Mô tả hệ thống hiện tại, diện tích mái, yêu cầu đặc biệt..."></textarea>
        </div>
        <button type="submit" class="btn btn-amber btn-block btn-lg" style="margin-top:20px">
          <i class="fa-solid fa-calendar-check"></i> XÁC NHẬN ĐẶT LỊCH KHẢO SÁT
        </button>
        <p style="text-align:center;margin-top:12px;font-size:.8rem;color:rgba(255,255,255,.5)">
          <i class="fa-solid fa-lock" style="font-size:.7rem"></i> Thông tin của bạn được bảo mật tuyệt đối
        </p>
      </form>
    </div>
  </div>
</section>