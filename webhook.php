<?php
// File: webhook.php (Thư mục gốc)
require_once __DIR__ . '/autoload.php';

use App\Controllers\BookingController;

// Lấy dữ liệu Webhook dạng JSON do SePay gửi sang
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!empty($data) && isset($data['content'])) {
    $content = $data['content']; // Nội dung chuyển khoản (Ví dụ: "DATSAN 15" hoặc "DATSAN15")
    $amount = $data['transferAmount'] ?? 0; // Số tiền nhận được

    // Dùng Regex tìm chữ "DATSAN" kèm theo ID đơn hàng
    if (preg_match('/DATSAN\s*(\d+)/i', $content, $matches)) {
        $bookingId = intval($matches[1]);

        // Gọi Controller xử lý cập nhật đơn & gửi mail
        $bookingController = new BookingController();
        $bookingController->confirmPaymentSuccess($bookingId);

        // Trả về kết quả thành công cho SePay
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Xác nhận thanh toán thành công']);
        exit();
    }
}

// Nếu không đúng định dạng
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin đơn hàng']);
