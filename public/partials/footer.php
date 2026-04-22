       <div id="deleteModal" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                <h3 class="modal-title">Xác nhận xóa dữ liệu</h3>
                <p class="modal-text">Bạn có chắc chắn muốn xóa dữ liệu này không? Hành động này sẽ không thể hoàn tác và xóa mọi dữ liệu liên quan.</p>
                <div class="modal-actions">
                    <button class="btn-confirm" id="confirmDeleteBtn">Xóa (OK)</button>
                    <button class="btn-cancel" onclick="closeModal()">Hủy (Cancel)</button>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="footer-main">
                <div class="container">
                    <div class="footer-grid">
                        <div class="footer-brand">
                            <a href="" class="logo" style="margin-bottom:16px">
                                <div class="logo-icon">
                                    <img class="logo-img-footer" src="../webctylvc/uploads/web/logo/lvc.jpg" alt="">
                                </div>
                                <div class="logo-text">
                                <div class="brand">LVC <span>SOLAR</span></div>
                                <div class="tagline">Giải pháp năng lượng sạch</div>
                                </div>
                            </a>
                            <p class="footer-desc">Chuyên thiết kế, thi công và cung cấp vật tư năng lượng mặt trời chất lượng cao. Kiến tạo giá trị bền vững cho tương lai xanh của Việt Nam.</p>
                            <div class="footer-socials">
                                <a href="#" class="social-btn"><i class="fa-brands fa-facebook-f"></i></a>
                                <a href="#" class="social-btn"><i class="fa-brands fa-youtube"></i></a>
                                <a href="#" class="social-btn"><i class="fa-brands fa-tiktok"></i></a>
                                <a href="#" class="social-btn"><i class="fa-brands fa-zalo" style="font-style:normal;font-size:.7rem;font-weight:800">Z</i></a>
                            </div>
                        </div>

                        <!-- Links -->
                        <div class="footer-col">
                            <h4>Dịch vụ</h4>
                            <div class="footer-links">
                                <a href="?page=services#lap-dat"><i class="fa-solid fa-chevron-right" style="font-size:.65rem;color:var(--amber)"></i> Lắp đặt trọn gói</a>
                                <a href="?page=services#ve-sinh"><i class="fa-solid fa-chevron-right" style="font-size:.65rem;color:var(--amber)"></i> Vệ sinh tấm pin</a>
                                <a href="?page=services#bao-tri"><i class="fa-solid fa-chevron-right" style="font-size:.65rem;color:var(--amber)"></i> Bảo trì hệ thống</a>
                                <a href="?page=services#nang-cap"><i class="fa-solid fa-chevron-right" style="font-size:.65rem;color:var(--amber)"></i> Nâng cấp inverter</a>
                                <a href="?page=services#booking"><i class="fa-solid fa-chevron-right" style="font-size:.65rem;color:var(--amber)"></i> Đặt lịch khảo sát</a>
                            </div>
                        </div>

                        <div class="footer-col">
                            <h4>Thông tin</h4>
                            <div class="footer-links">
                                <a href="blog.php"><i class="fa-solid fa-chevron-right" style="font-size:.65rem;color:var(--amber)"></i> Blog & Kiến thức</a>
                                <a href="projects.php"><i class="fa-solid fa-chevron-right" style="font-size:.65rem;color:var(--amber)"></i> Dự án tiêu biểu</a>
                                <a href="products.php"><i class="fa-solid fa-chevron-right" style="font-size:.65rem;color:var(--amber)"></i> Vật tư & Sản phẩm</a>
                                <a href="contact.php"><i class="fa-solid fa-chevron-right" style="font-size:.65rem;color:var(--amber)"></i> Liên hệ tư vấn</a>
                                <a href="#"><i class="fa-solid fa-chevron-right" style="font-size:.65rem;color:var(--amber)"></i> Chính sách bảo hành</a>
                            </div>
                        </div>

                        <!-- Contact -->
                        <div class="footer-col">
                            <h4>Liên hệ</h4>
                            <div class="footer-contact-item">
                                <i class="fa-solid fa-location-dot ic"></i>
                                <span><?= e($setting['address'] ?? '123 Đường Công Nghệ, TP. Bạc Liêu, Tỉnh Bạc Liêu') ?></span>
                            </div>
                            <div class="footer-contact-item">
                                <i class="fa-solid fa-phone ic"></i>
                                <span><?= e($setting['hotline'] ?? '0912.345.678') ?></span>
                            </div>
                            <div class="footer-contact-item">
                                <i class="fa-solid fa-envelope ic"></i>
                                <span><?= e($setting['email'] ?? 'tuongpm@ctylvc.com.vn') ?></span>
                            </div>
                            <div class="footer-contact-item">
                                <i class="fa-solid fa-clock ic"></i>
                                <span>Thứ 2 – Thứ 7 · 8:00 – 17:30</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

              <div class="footer-bottom">
                <div class="container">
                <div class="footer-bottom-inner">
                    <span>&copy; <?= date('Y') ?> Bản quyền thuộc về <strong style="color:rgba(255,255,255,.7)">CTY LVC SOLAR</strong>. Mọi quyền được bảo lưu.</span>
                    <span>Thiết kế & Phát triển bởi <strong style="color:var(--amber-lt)">LVC Tech</strong></span>
                </div>
                </div>
            </div>
        </footer>
        <div class="floating-contact">
            <a href="https://zalo.me/0912345678" target="_blank" class="contact-btn zalo-btn">
                <div class="ring-circle"></div>
                <div class="ring-circle-fill"></div>
                <div class="icon-wrap">
                    <span>Zalo</span> 
                </div>
            </a>

            <a href="tel:0912345678" class="contact-btn phone-btn">
                <div class="ring-circle"></div>
                <div class="ring-circle-fill"></div>
                <div class="icon-wrap">
                    <i class="fa-solid fa-phone"></i>
                </div>
            </a>
        </div>
        
        <div id="globalLoading" class="global-loading-overlay">
            <div class="loading-spinner"></div>
            <div class="loading-text" id="loadingText">Đang xử lý, vui lòng đợi...</div>
        </div>
        
        <script src="assets/js/modal.js"></script>
        <script src="assets/js/showloading.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
            const message = sessionStorage.getItem('pendingToast');
            const type = sessionStorage.getItem('pendingToastType');

            if (message) {
                // Đợi 500ms cho trang chủ hiện lên mượt rồi mới bắn Toast
                setTimeout(() => {
                    showToast(message, type);
                }, 500);

                // Xóa ngay để không bị hiện lại khi F5
                sessionStorage.removeItem('pendingToast');
                sessionStorage.removeItem('pendingToastType');
            }
        });
        </script>
        <script src="assets/js/add-product.js"></script>
    </body>
</html>