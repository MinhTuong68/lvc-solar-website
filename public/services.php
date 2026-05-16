<?php
    // GỌI FILE CHỨA CÁC HÀM XỬ LÝ (Điều chỉnh lại đường dẫn nếu cần)
    include("../classes/services.php"); 
    
    // Khởi tạo đối tượng
    $service_obj = new Service($conn);
    
    // Kéo toàn bộ danh sách "Loại dịch vụ" từ Database ra và nhét vào biến $allTypes
    $allTypes = $service_obj->getAllServiceTypes();
?>
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

        <hr class="srv-divider">
        <!-- Vệ sinh -->
        <div id="ve-sinh" class="srv-grid">
            <div class="srv-img-wrap">
                <img src="https://images.unsplash.com/photo-1466611653911-95081537e5b7?q=80&w=800" alt="Vệ sinh tấm pin">
            </div>
            <div class="lvc-srv-container">
                <span class="section-label">Dịch vụ 02</span>
                <h2 class="lvc-srv-heading">Vệ Sinh Tấm Pin Chuyên Nghiệp</h2>
                <p class="lvc-srv-text">Bụi bẩn, rêu mốc và chất bẩn tích tụ có thể giảm hiệu suất tấm pin lên đến 30%. Vệ sinh định kỳ 1–2 lần/năm giúp duy trì hiệu suất tối đa và kéo dài tuổi thọ thiết bị.</p>
                
                <div class="lvc-srv-grid">
                    <?php foreach ([['🚿','Máy rửa áp lực','Không trầy xước bề mặt'],['🧪','Hóa chất chuyên dụng','An toàn, không ăn mòn'],['📊','Kiểm tra hiệu suất','Đo sản lượng trước/sau'],['🛡️','Bảo hiểm thi công','An toàn tuyệt đối']] as [$ic,$title,$desc]): ?>
                        <div class="lvc-srv-card">
                            <div class="lvc-srv-icon"><?= $ic ?></div>
                            <div class="lvc-srv-card-title"><?= $title ?></div>
                            <div class="lvc-srv-card-desc"><?= $desc ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <a href="#booking" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-calendar-check"></i> Đặt lịch vệ sinh
                </a>
            </div>
        </div>

        <hr class="srv-divider">

        <!-- Bảo trì -->
        <div id="bao-tri" class="srv-grid">
            <div class="srv-content">
                <span class="section-label">Dịch vụ 03</span>
                <h2 class="srv-title">Bảo Trì & Sửa Chữa Hệ Thống</h2>
                <p class="srv-desc">Phát hiện và khắc phục sự cố nhanh chóng, đảm bảo hệ thống của bạn luôn vận hành ổn định. Chúng tôi hỗ trợ tất cả các thương hiệu inverter và tấm pin.</p>
                
                <div class="srv-price-box">
                    <div class="price-header">Bảng giá bảo trì định kỳ</div>
                    
                    <?php foreach ([['Hệ thống ≤ 5kWp','350.000₫/lần'],['Hệ thống 5–15kWp','550.000₫/lần'],['Hệ thống ≥ 15kWp','Báo giá riêng'],['Gói bảo trì năm','Ưu đãi 20%']] as [$label,$price]): ?>
                        <div class="price-row">
                            <span class="label"><?= $label ?></span>
                            <strong class="value"><?= $price ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <a href="#booking" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-wrench"></i> Đặt lịch bảo trì
                </a>
            </div>

            <div class="srv-img-wrap">
                <img src="https://images.unsplash.com/photo-1548337138-e87d889cc369?q=80&w=800" alt="Bảo trì hệ thống solar" class="srv-img">
            </div>
        </div>

        <hr class="srv-divider">

        <!-- Nâng cấp -->
        <div id="nang-cap" class="srv-grid lvc-srv4-wrapper">
            <div class="srv-img-wrap">
                <img src="https://images.unsplash.com/photo-1613665813446-82a78c468a1d?q=80&w=800" alt="Nâng cấp inverter" class="lvc-srv4-img">
            </div>
            <div class="lvc-srv4-content">
                <span class="section-label">Dịch vụ 04</span>
                <h2 class="lvc-srv4-title">Nâng Cấp Inverter & Pin Lưu Trữ</h2>
                <p class="lvc-srv4-desc">Nâng cấp từ hệ thống hòa lưới đơn thuần sang hybrid, bổ sung pin lưu trữ Lithium để sử dụng điện vào ban đêm và khi mất điện lưới.</p>
                
                <ul class="lvc-srv4-list">
                <?php foreach (['Đánh giá khả năng nâng cấp hệ thống hiện tại','Tư vấn chọn pin lưu trữ phù hợp công suất','Lắp đặt inverter hybrid: Deye, Solis, Growatt','Cấu hình hệ thống tự động thông minh','Đào tạo sử dụng ứng dụng giám sát từ xa'] as $item): ?>
                    <li class="lvc-srv4-list-item">
                        <i class="fa-solid fa-circle-check lvc-srv4-icon"></i> <?= $item ?>
                    </li>
                <?php endforeach; ?>
                </ul>
                
                <a href="#booking" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-bolt"></i> Tư vấn nâng cấp
                </a>
            </div>
        </div>
    </div>   
</section>

<section id="booking" class="srv-booking2">
  <div class="container">
    <div class="section-header">
        <span class="section-label lvc-book-label-amber">Đặt lịch ngay</span>
        <h2 class="section-title lvc-book-title-white">Đặt Lịch Khảo Sát Miễn Phí</h2>
        <p class="section-sub lvc-book-sub-text">Điền thông tin bên dưới, đội kỹ thuật LVC Solar sẽ liên hệ lại trong vòng 30 phút và sắp xếp lịch khảo sát phù hợp.</p>
    </div>
    <div id="booking-form" class="lvc-book-form-card">
      <form method="POST" action="actions/add-service.php" onsubmit="showLoading('Đang gửi yêu cầu, vui lòng đợi...');">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="form-row lvc-book-grid-row">
          <div class="form-group">
            <label class="lvc-book-lbl">Họ và tên <span class="lvc-book-req">*</span></label>
            <input type="text" name="customer_name" class="form-control lvc-book-input" placeholder="Nguyễn Văn A" required>
          </div>
          <div class="form-group">
            <label class="lvc-book-lbl">Số điện thoại <span class="lvc-book-req">*</span></label>
            <input type="tel" name="phone" class="form-control lvc-book-input" placeholder="0912 345 678" required>
          </div>
        </div>
        <div class="form-group lvc-book-mt">
          <label class="lvc-book-lbl">Địa chỉ lắp đặt</label>
          <input type="text" name="address" class="form-control lvc-book-input" placeholder="Số nhà, đường, phường/xã, tỉnh/thành">
        </div>
        <div class="lvc-book-grid-mt">
          <div class="form-group">
            <label class="lvc-book-lbl">Loại dịch vụ</label>
            <select name="service_type" class="form-control lvc-book-select">
              <option value="">-- Chọn dịch vụ cần tư vấn --</option>
              <?php 
              // Kiểm tra xem biến $allTypes đã có dữ liệu từ Database chưa
              if(isset($allTypes) && !empty($allTypes)): 
                  foreach($allTypes as $type): 
              ?>
                  <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['service_type']) ?></option>
              <?php 
                  endforeach; 
              else: 
              ?>
                  <option value="">Lỗi: Chưa tải được dữ liệu dịch vụ</option>
              <?php 
              endif; 
              ?>
          </select>
          </div>
          <div class="form-group">
              <label class="lvc-book-lbl">Tiền điện TB/tháng</label>
              <input type="text" name="electricity_bill" class="form-control lvc-book-input" placeholder="VD: 1.500.000₫">
          </div>
        </div>
        <div class="form-group lvc-book-mt ">
          <label class="lvc-book-lbl">Ngày muốn khảo sát</label>
          <input type="date" name="booking_date" class="form-control lvc-book-input" min="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group lvc-book-mt">
          <label class="lvc-book-lbl">Mô tả thêm (vấn đề, yêu cầu đặc biệt...)</label>
          <textarea name="issue_description" class="form-control lvc-book-textarea" rows="3" placeholder="Mô tả hệ thống hiện tại, diện tích mái, yêu cầu đặc biệt..."></textarea>
        </div>
        <button type="submit" name="btn_submit_service" class="btn btn-amber btn-block btn-lg lvc-book-mt">
          <i class="fa-solid fa-calendar-check"></i> XÁC NHẬN ĐẶT LỊCH KHẢO SÁT
        </button>
        <p class="p-srv-info">
          <i class="fa-solid fa-lock" style="font-size:.7rem"></i> Thông tin của bạn được bảo mật tuyệt đối
        </p>
      </form>
    </div>
  </div>
</section>