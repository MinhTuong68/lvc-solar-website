<?php
    // Kiểm tra session đã bật chưa, chưa thì bật lên

    // Bắt sự kiện khi người dùng bấm Submit Form
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_test'])) {
        
        // 1. Hứng dữ liệu từ form
        $name = $_POST['test_name']; 
        // 3. Nhét thông báo vào túi Session
        $_SESSION['toast_message'] = "🎉 Chúc mừng <b>$name</b>! Luồng PHP đã chạy thành công!";
        $_SESSION['toast_type'] = 'success';
        
        // 4. Chuyển trang bằng Javascript (Để tránh lỗi Header)
        // Chuyển nó về lại chính trang manage-dashboard
        echo "<script>
                window.location.href = 'index.php?page=manage-dashboard';
            </script>";
        exit(); 
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_delete'])){
        $name = $_POST['test_name']; 
        $_SESSION['toast_message'] = "🎉 Xóa <b>$name</b>! thành công!";
        $_SESSION['toast_type'] = 'error';
        echo "<script>
                window.location.href = 'index.php?page=manage-dashboard';
            </script>";
        exit(); 
    }
?>

<div class="wrapper">
    <div class="page-header">
        <h2 class="page-title">TEST LUỒNG PHP VÀ TOAST</h2>
    </div>
    <br>
    
    <form action="" method="POST" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); width: 400px;">
        
        <label style="font-weight: bold; margin-bottom: 10px; display: block;">Nhập tên của bạn để test:</label>
        
        <input type="text" name="test_name" class="form-control" value="Tường" style="width: 100%; margin-bottom: 20px;" required>
        
        <button type="submit" name="btn_test" class="btn-add" style="width: 100%; cursor: pointer; border: none;">
            <i class="fa-solid fa-paper-plane"></i> GỬI LÊN SERVER (PHP)
        </button>
        <button type="submit" name="btn_delete" class="btn-add" style="width: 100%; cursor: pointer; border: none;">
            <i class="fa-solid fa-paper-plane"></i> Xóa
        </button>
    </form>
</div>