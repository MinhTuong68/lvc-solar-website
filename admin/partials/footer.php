    <?php if (isset($_SESSION['toast_message'])) { 
            $type = isset($_SESSION['toast_type']) ? $_SESSION['toast_type'] : 'success';
        ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast("<?= $_SESSION['toast_message'] ?>", "<?= $type ?>");
                });
            </script>
            
            <?php 
                unset($_SESSION['toast_message']); 
                unset($_SESSION['toast_type']); 
            ?>
        <?php } ?>

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
    </body>
</html>