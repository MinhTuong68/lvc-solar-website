<?php
include('../classes/setting.php');
$settingObj = new Setting($conn);
$settings = $settingObj->getSettings();
?>

<div class="main-content">
    <div class="wrapper">
        <h1>Cài đặt hệ thống</h1>
        <br><br>

        <?php 
            if(isset($_SESSION['add'])) {
                echo $_SESSION['add'];
                unset($_SESSION['add']);
            }
        ?>

        <form action="actions/update-setting.php" method="POST" enctype="multipart/form-data">
            <table class="tbl-30">
                <tr>
                    <td colspan="2"><h3>1. Thông tin chung & Liên hệ</h3></td>
                </tr>
                <tr>
                    <td>Tên Website:</td>
                    <td>
                        <input type="text" name="site_name" value="<?= $settings['site_name'] ?? '' ?>" class="form-control" required>
                    </td>
                </tr>
                <tr>
                    <td>Hotline:</td>
                    <td>
                        <input type="text" name="hotline" value="<?= $settings['hotline'] ?? '' ?>" class="form-control">
                    </td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td>
                        <input type="email" name="email" value="<?= $settings['email'] ?? '' ?>" class="form-control">
                    </td>
                </tr>
                <tr>
                    <td>Địa chỉ:</td>
                    <td>
                        <textarea name="address" rows="3" class="form-control"><?= $settings['address'] ?? '' ?></textarea>
                    </td>
                </tr>

                <tr>
                    <td>Site description:</td>
                    <td>
                        <textarea name="site_description" rows="3" class="form-control"><?= $settings['site_description'] ?? '' ?></textarea>
                    </td>
                </tr>

                <tr>
                    <td colspan="2"><br><h3>2. Logo Website</h3></td>
                </tr>
                <tr>
                    <td>Logo hiện tại:</td>
                    <td>
                        <?php if(!empty($settings['logo'])): ?>
                            <img src="<?php echo ROOT_URL ?>uploads/web/logo/<?= $settings['logo'] ?>" width="150px">
                        <?php else: ?>
                            <span class="error">Chưa có logo</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Chọn Logo mới:</td>
                    <td>
                        <input type="file" name="logo" accept="image/*">
                    </td>
                </tr>

                <tr>
                    <td colspan="2"><br><h3>3. Liên kết mạng xã hội</h3></td>
                </tr>
                <tr>
                    <td>Facebook:</td>
                    <td>
                        <input type="url" name="facebook" value="<?= $settings['facebook_link'] ?? '' ?>" class="form-control" placeholder="https://facebook.com/...">
                    </td>
                </tr>
                <tr>
                    <td>YouTube:</td>
                    <td>
                        <input type="url" name="youtube" value="<?= $settings['youtube_link'] ?? '' ?>" class="form-control" placeholder="https://youtube.com/...">
                    </td>
                </tr>

                <tr>
                    <td>Zalo:</td>
                    <td>
                        <input type="url" name="zalo" value="<?= $settings['zalo_link'] ?? '' ?>" class="form-control" placeholder="Link Zalo...">
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <br>
                        <input type="submit" name="submit" value="Lưu cấu hình" class="btn-secondary">
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<style>
    /* CSS bổ sung để giao diện gọn gàng hơn */
    .form-control {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    h3 {
        color: #1e3a8a;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 5px;
        margin-top: 15px;
    }
    .tbl-30 { width: 60%; } /* Chỉnh độ rộng bảng cho vừa mắt */
</style>