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

    /**
     * API Ajax trả về các khung giờ đã bị đặt trong ngày
     */
    public function checkSlotsApi($courtId, $date)
    {
        header('Content-Type: application/json');
        echo json_encode($this->bookingModel->getBookedSlots($courtId, $date));
        exit();
    }

    /**
     * Xử lý Submit Form Đặt Sân, tạo mã VietQR & Gửi Mail Hóa Đơn
     */
    public function processBooking($postData, $userId)
    {
        $courtId = intval($postData['court_id'] ?? 0);
        $date = $postData['booking_date'] ?? '';
        $startHour = intval($postData['start_hour'] ?? 0);
        $endHour = intval($postData['end_hour'] ?? 0);
        $selectedServices = $postData['services'] ?? [];

        // Lấy Email từ ô nhập liệu trên Form
        $customerEmail = trim($postData['customer_email'] ?? '');

        // 1. Kiểm tra tính hợp lệ của dữ liệu đầu vào
        if (empty($date) || $startHour <= 0 || $endHour <= 0 || $endHour <= $startHour) {
            return ['error' => 'Thông tin ngày và giờ đặt sân không hợp lệ!'];
        }

        if (empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Địa chỉ email nhận hóa đơn không hợp lệ!'];
        }

        $startTimeStr = sprintf('%02d:00:00', $startHour);
        $endTimeStr = sprintf('%02d:00:00', $endHour);

        // 2. Kiểm tra trùng lịch đặt
        if ($this->bookingModel->isSlotOverlapped($courtId, $date, $startTimeStr, $endTimeStr)) {
            return ['error' => 'Khung giờ này đã có người đặt, vui lòng chọn khung giờ khác!'];
        }

        // 3. Tạo đơn đặt sân mới vào CSDL
        $bookingId = $this->bookingModel->createBooking($userId, $courtId, $date, $startTimeStr, $endTimeStr);

        if ($bookingId) {
            // 4. Tính toán tổng chi phí (Sân + Dịch vụ đi kèm)
            $court = $this->courtModel->getCourtById($courtId);
            $duration = $endHour - $startHour;
            $pricePerHour = $court['price_per_hour'] ?? $court['price'] ?? 150000;
            $totalMoney = $duration * $pricePerHour;

            $allServices = $this->serviceModel->getAllServices();
            foreach ($allServices as $srv) {
                if (in_array($srv['id'], $selectedServices)) {
                    $totalMoney += ($srv['unit'] === 'giờ') ? ($srv['price'] * $duration) : $srv['price'];
                }
            }

            // 5. CẤU HÌNH THÔNG TIN TÀI KHOẢN NGÂN HÀNG CỦA BẠN (TẠO MÃ VIETQR)
            $bankId = "MB";                 // Mã ngân hàng (VD: MB, VCB, TCB, ACB, VPB, ICB...)
            $accountNo = "0339936910";      // Số tài khoản ngân hàng của bạn
            $accountName = "PHAM MINH VU"; // Tên chủ tài khoản (Viết hoa không dấu)

            $desc = "DATSAN " . $bookingId;
            $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.png?amount={$totalMoney}&addInfo=" . urlencode($desc) . "&accountName=" . urlencode($accountName);

            // 6. TỰ ĐỘNG GỬI EMAIL HÓA ĐƠN XÁC NHẬN CHO KHÁCH HÀNG
            $user = $this->userModel->getUserById($userId);
            $userName = $user['fullname'] ?? $user['name'] ?? $user['username'] ?? 'Khách hàng';
            $timeSlot = sprintf('%02d:00 - %02d:00', $startHour, $endHour);

            Mailer::sendInvoice(
                $customerEmail,
                $userName,
                $bookingId,
                $court['name'],
                $date,
                $timeSlot,
                $totalMoney
            );

            return [
                'success' => true,
                'booking_id' => $bookingId,
                'qr_url' => $qrUrl,
                'message' => "Đặt sân thành công! Hóa đơn điện tử đã được gửi đến email: " . htmlspecialchars($customerEmail)
            ];
        }

        return ['error' => 'Lỗi hệ thống, không thể đặt sân!'];
    }

    /**
     * API Ajax trả về trạng thái thanh toán hiện tại của đơn hàng (cho JavaScript ở giao diện)
     */
    public function checkPaymentStatusApi($bookingId)
    {
        header('Content-Type: application/json');
        $booking = $this->bookingModel->getBookingById($bookingId);

        if ($booking) {
            echo json_encode(['status' => $booking['status']]); // Trả về 'pending', 'confirmed', ...
        } else {
            echo json_encode(['status' => 'not_found']);
        }
        exit();
    }

    /**
     * Hàm kích hoạt khi SePay báo đã nhận tiền (Cập nhật DB -> Gửi Mail Hóa Đơn)
     */
    public function confirmPaymentSuccess($bookingId)
    {
        $booking = $this->bookingModel->getBookingById($bookingId);

        // Nếu tìm thấy đơn hàng và trạng thái vẫn đang là chờ thanh toán (pending)
        if ($booking && $booking['status'] === 'pending') {
            // 1. Đổi trạng thái trong CSDL thành 'confirmed' (Đã thanh toán)
            $this->bookingModel->updateStatus($bookingId, 'confirmed');

            // 2. Lấy thông tin sân để tạo hóa đơn
            $court = $this->courtModel->getCourtById($booking['court_id']);

            $startHour = intval(explode(':', $booking['start_time'])[0]);
            $endHour = intval(explode(':', $booking['end_time'])[0]);
            $timeSlot = sprintf('%02d:00 - %02d:00', $startHour, $endHour);

            // 3. Gửi Email Hóa Đơn cho khách hàng
            $customerEmail = !empty($booking['customer_email']) ? $booking['customer_email'] : 'khachhang@gmail.com';

            \App\Core\Mailer::sendInvoice(
                $customerEmail,
                'Khách hàng',
                $bookingId,
                $court['name'],
                $booking['booking_date'],
                $timeSlot,
                $booking['total_amount'] ?? 0
            );

            return true;
        }
        return false;
    }
}
