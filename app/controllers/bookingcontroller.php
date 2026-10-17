<?php

namespace App\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Service;
use App\Models\User;
use App\Core\Mailer;

class BookingController
{
    private $bookingModel;
    private $courtModel;
    private $serviceModel;
    private $userModel;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->courtModel = new Court();
        $this->serviceModel = new Service();
        $this->userModel = new User();
    }

    public function checkSlotsApi($courtId, $date)
    {
        header('Content-Type: application/json');
        // Tự động hủy các đơn quá 3 phút chưa thanh toán trước khi trả về danh sách slot
        $this->bookingModel->cancelExpiredBookings();

        $bookedSlots = $this->bookingModel->getBookedSlotsByDate($courtId, $date);
        echo json_encode($bookedSlots);
        exit;
    }

    // API kiểm tra trạng thái thanh toán và tự động gửi email hóa đơn ngay khi nhận tiền
    public function checkPaymentStatusApi($bookingId)
    {
        header('Content-Type: application/json');

        // Kiểm tra xem đơn đã quá 3 phút chưa thanh toán chưa
        $this->bookingModel->cancelExpiredBookings();

        $booking = $this->bookingModel->getBookingById($bookingId);

        if (!$booking || $booking['status'] === 'cancelled') {
            echo json_encode(['status' => 'expired']);
            exit;
        }

        // Kiểm tra nếu đã thanh toán (qua SePay webhook hoặc cập nhật thủ công thành confirmed/paid)
        if ((isset($booking['status']) && $booking['status'] === 'confirmed') || (isset($booking['payment_status']) && $booking['payment_status'] === 'paid')) {

            // Kiểm tra xem đã gửi email chưa (tránh gửi trùng lặp mỗi khi Ajax gọi check)
            if (empty($booking['email_sent'])) {
                $this->sendSuccessEmail($booking);
                $this->bookingModel->markEmailSent($bookingId);
            }

            echo json_encode(['status' => 'confirmed']);
        } else {
            echo json_encode(['status' => 'pending']);
        }
        exit;
    }

    // Phương thức xử lý xác nhận thanh toán được gọi trực tiếp từ webhook.php
    public function confirmPaymentSuccess($bookingId)
    {
        header('Content-Type: application/json');

        $booking = $this->bookingModel->getBookingById($bookingId);

        if ($booking) {
            // Cập nhật trạng thái đơn đặt sân thành confirmed
            if ($booking['status'] !== 'confirmed') {
                $this->bookingModel->updateStatus($bookingId, 'confirmed');
                $booking['status'] = 'confirmed';
            }

            // Tiến hành gửi email hóa đơn nếu chưa được gửi
            if (empty($booking['email_sent'])) {
                $this->sendSuccessEmail($booking);
                $this->bookingModel->markEmailSent($bookingId);
            }

            echo json_encode(['success' => true, 'message' => 'Xác nhận thanh toán và gửi email thành công!']);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn đặt sân!']);
        exit;
    }

    private function sendSuccessEmail($booking)
    {
        try {
            $user = $this->userModel->getUserById($booking['user_id']);
            $userName = $user['fullname'] ?? $user['name'] ?? $user['username'] ?? 'Khách hàng';

            $court = $this->courtModel->getCourtById($booking['court_id']);
            $courtName = $court['name'] ?? 'Sân cầu lông';

            // Tính thời gian hiển thị
            $start = strtotime($booking['start_time']);
            $end = strtotime($booking['end_time']);
            $timeSlot = date('H:i', $start) . ' - ' . date('H:i', $end);

            // Lấy email khách (nếu có lưu hoặc lấy từ user)
            $customerEmail = $user['email'] ?? '';
            if (!empty($booking['customer_email'])) {
                $customerEmail = $booking['customer_email'];
            }

            if (!empty($customerEmail)) {
                Mailer::sendInvoice(
                    $customerEmail,
                    $userName,
                    $booking['id'],
                    $courtName,
                    $booking['booking_date'],
                    $timeSlot,
                    $booking['total_amount'] ?? 0
                );
            }
        } catch (\Exception $e) {
            // Xử lý ngoại lệ gửi email nếu có
        }
    }

    public function processBooking($postData, $userId)
    {
        // Ưu tiên lấy user_id từ Session nếu $userId truyền vào bị bằng 0
        if (empty($userId) && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }

        // Hủy các đơn quá hạn trước khi tạo đơn mới
        $this->bookingModel->cancelExpiredBookings();

        $courtId = intval($postData['court_id'] ?? 0);
        $date = $postData['booking_date'] ?? '';
        $slots = $postData['slots'] ?? [];
        $selectedServices = $postData['services'] ?? [];
        $customerEmail = trim($postData['customer_email'] ?? '');

        if (empty($date) || empty($slots) || !is_array($slots)) {
            return ['error' => 'Vui lòng chọn ít nhất một khung giờ đặt sân!'];
        }

        if (empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Địa chỉ email nhận hóa đơn không hợp lệ!'];
        }

        sort($slots);
        $startTimeStr = reset($slots) . ':00';

        $lastSlot = end($slots);
        $lastSlotParts = explode(':', $lastSlot);
        $endTimestamp = strtotime("{$lastSlotParts[0]}:{$lastSlotParts[1]} +30 minutes");
        $endTimeStr = date('H:i:s', $endTimestamp);

        foreach ($slots as $slotLabel) {
            $slotStart = $slotLabel . ':00';
            $slotEndTimestamp = strtotime("{$slotLabel} +30 minutes");
            $slotEnd = date('H:i:s', $slotEndTimestamp);

            if ($this->bookingModel->isSlotOverlapped($courtId, $date, $slotStart, $slotEnd)) {
                return ['error' => "Khung giờ {$slotLabel} đã có người đặt, vui lòng chọn lại!"];
            }
        }

        // Tính tổng tiền
        $court = $this->courtModel->getCourtById($courtId);
        $pricePerHour = floatval($court['price_per_hour'] ?? $court['price'] ?? 150000);
        $slotCount = count($slots);
        $courtTotal = ($pricePerHour / 2) * $slotCount;
        $totalMoney = $courtTotal;

        $allServices = $this->serviceModel->getAllServices();
        foreach ($allServices as $srv) {
            if (in_array($srv['id'], $selectedServices)) {
                $totalMoney += floatval($srv['price']);
            }
        }

        // Tạo đơn hàng với trạng thái 'pending' và lưu tổng tiền + email nhận
        $bookingId = $this->bookingModel->createBookingWithDetails($userId, $courtId, $date, $startTimeStr, $endTimeStr, $totalMoney, $customerEmail);

        if ($bookingId) {
            $bankId = "MB";
            $accountNo = "0339936910";
            $accountName = "PHAM MINH VU";

            $desc = "DATSAN " . $bookingId;
            $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.png?amount={$totalMoney}&addInfo=" . urlencode($desc) . "&accountName=" . urlencode($accountName);

            return [
                'success' => true,
                'booking_id' => $bookingId,
                'qr_url' => $qrUrl,
                'email' => $customerEmail
            ];
        }

        return ['error' => 'Lỗi hệ thống, không thể đặt sân!'];
    }
}
