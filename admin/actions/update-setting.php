<?php
include('../../config/constants.php');
include('../../classes/setting.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $settingObj = new Setting($conn);
    $current = $settingObj->getSettings();

    $site_name        = mysqli_real_escape_string($conn, $_POST['site_name']);
    $site_description = mysqli_real_escape_string($conn, $_POST['site_description']);
    $hotline          = mysqli_real_escape_string($conn, $_POST['hotline']);
    $email            = mysqli_real_escape_string($conn, $_POST['email']);
    $address          = mysqli_real_escape_string($conn, $_POST['address']);
    $facebook_link    = mysqli_real_escape_string($conn, $_POST['facebook']);
    $youtube_link     = mysqli_real_escape_string($conn, $_POST['youtube']);
    $zalo_link        = mysqli_real_escape_string($conn, $_POST['zalo']);

    $logo = $current['logo'];
    if (isset($_FILES['logo']['name']) && $_FILES['logo']['name'] != "") {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $logo_name = "logo_site_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], "../../uploads/web/logo/" . $logo_name)) {
            $logo = $logo_name; 
            $old_logo_path = "../../uploads/web/logo/" . $current['logo'];

            if ($current['logo'] != "" && file_exists($old_logo_path)) {
                unlink($old_logo_path);
            }
        }
    }

    // GỌI HÀM VỚI ĐÚNG 9 BIẾN THEO THỨ TỰ
    $res = $settingObj->updateSettings($site_name, $site_description, $hotline, $email, $address, $facebook_link, $youtube_link, $zalo_link, $logo);

    $_SESSION['add'] = $res ? "<div class='success'>Cập nhật thành công!</div>" : "<div class='error'>Lỗi cập nhật!</div>";
    header("Location: ../index.php?page=manage-setting");
    exit();
}
?>