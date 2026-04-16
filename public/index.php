<?php 
    session_start();
    include("partials/header.php");
    include("partials/menu.php");
?>
 
 <?php
    if (isset($page_map[$current_page])) {
        include($page_map[$current_page]);
    } else {
        echo "<h3 style='color:red;'>Lỗi 404: Trang bạn tìm không tồn tại hoặc không có quyền truy cập!</h3>";
    }
?>

<?php include("partials/footer.php") ?>