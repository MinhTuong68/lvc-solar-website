<?php
require_once '../classes/products.php';

// 1. Tạo kết nối thực tế (Ví dụ dùng PDO)
$host = 'localhost';
$db   = 'lvc_solar_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    
    // 2. Truyền kết nối vào Class
    $product = new Product($pdo);

    // 3. Kiểm tra xem nó "hút" được kết nối chưa
    echo $product->testConnection();

} catch (PDOException $e) {
    echo "❌ Kết nối CSDL thất bại: " . $e->getMessage();
}
?>