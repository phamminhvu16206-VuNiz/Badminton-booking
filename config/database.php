<?php
// Chức năng: Kết nối cơ sở dữ liệu MySQL bằng PDO cho toàn bộ hệ thống

$host = 'localhost';
$dbname = 'badminton_booking';
$username = 'root';
$password = '';

try {
    // Khởi tạo kết nối PDO với charset utf8mb4
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Bật chế độ báo lỗi ngoại lệ (Exception) để dễ dàng bắt lỗi khi query sai
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Thiết lập kiểu dữ liệu trả về mặc định là mảng kết hợp (Associative Array)
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Dừng chương trình và thông báo lỗi nếu kết nối thất bại
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}
?>