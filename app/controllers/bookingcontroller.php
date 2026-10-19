<?php

namespace App\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Service;
use App\Models\User;
use App\Core\Mailer;

class BookingController extends BaseController
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

    public function index($id = null)
    {
        if (isset($_GET['action']) && $_GET['action'] === 'check_slots') {
            $this->checkSlotsApi(
                (int) ($_GET['court_id'] ?? 0),
                (int) ($_GET['court_number'] ?? 0),
                $_GET['date'] ?? date('Y-m-d')
            );
        }
        if (isset($_GET['action']) && $_GET['action'] === 'check_payment') {
            $this->checkPaymentStatusApi((int) ($_GET['booking_id'] ?? 0));
        }

        $bookingResult = null;
        if (isset($_POST['btn_submit_booking'])) {
            $bookingResult = $this->processBooking($_POST, (int) ($_SESSION['user_id'] ?? 0));
        }

        $courtId = (int) ($_GET['court_id'] ?? ($_GET['id'] ?? 1));
        $court = $this->courtModel->getCourtById($courtId);
        if ($court && ($court['status'] ?? 'active') !== 'active') {
            http_response_code(503);
            $error = 'Sân này hiện đang bảo trì và chưa thể nhận đặt sân.';
            $this->render('client/court-detail', compact('court', 'error'));
            return;
        }
        $services = $this->serviceModel->getAllServices();
        if (empty($services)) {
            $services = [
                ['id' => 1, 'name' => 'Thuê vợt cầu lông', 'price' => 30000, 'unit' => 'giờ'],
                ['id' => 2, 'name' => 'Mua 1 ống cầu lông', 'price' => 25000, 'unit' => 'ống'],
                ['id' => 3, 'name' => 'Nước suối (2 chai)', 'price' => 10000, 'unit' => 'lần'],
            ];
        }

        $pricePerHour = 150000;
        if ($court) {
            $pricePerHour = (float) ($court['price_per_hour'] ?? $court['price'] ?? $court['gia'] ?? 150000);
        } else {
            $court = [
                'id' => $courtId,
                'name' => 'Sân Cầu Lông Số ' . $courtId,
                'address' => '123 Đường Nguyễn Văn Linh, Quận 7, TP.HCM',
                'image' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=600',
            ];
        }

        $this->render('client/booking', compact('bookingResult', 'court', 'services', 'pricePerHour'));
    }

    public function mine($id = null)
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để quản lý lịch đặt sân.';
            header('Location: ' . APP_BASE_URL . '/auth/login');
            exit;
        }

        $success = $_SESSION['booking_success'] ?? '';
        $error = $_SESSION['booking_error'] ?? '';
        unset($_SESSION['booking_success'], $_SESSION['booking_error']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_cancellation'])) {
            if (!$this->hasValidCsrfToken($_POST['csrf_token'] ?? null)) {
                $_SESSION['booking_error'] = 'Phiên gửi yêu cầu không hợp lệ. Vui lòng thử lại.';
            } else {
                $bookingId = (int) ($_POST['booking_id'] ?? 0);
                $reasonInput = $_POST['cancellation_reason'] ?? '';
                $bankNameInput = $_POST['cancellation_bank_name'] ?? '';
                $bankAccountNameInput = $_POST['cancellation_bank_account_name'] ?? '';
                $bankAccountNumberInput = $_POST['cancellation_bank_account_number'] ?? '';
                $reason = is_string($reasonInput) ? trim($reasonInput) : '';
                $bankName = is_string($bankNameInput) ? trim($bankNameInput) : '';
                $bankAccountName = is_string($bankAccountNameInput) ? trim($bankAccountNameInput) : '';
                $bankAccountNumber = is_string($bankAccountNumberInput) ? trim($bankAccountNumberInput) : '';
                if (
                    $bookingId <= 0
                    || $reason === ''
                    || mb_strlen($reason) > 1000
                    || $bankName === ''
                    || mb_strlen($bankName) > 100
                    || $bankAccountName === ''
                    || mb_strlen($bankAccountName) > 150
                    || $bankAccountNumber === ''
                    || mb_strlen($bankAccountNumber) > 50
                ) {
                    $_SESSION['booking_error'] = 'Vui lòng nhập lý do hủy và đầy đủ thông tin tài khoản nhận hoàn tiền hợp lệ.';
                } elseif ($this->bookingModel->requestCancellation($bookingId, $userId, $reason, $bankName, $bankAccountName, $bankAccountNumber)) {
                    $_SESSION['booking_success'] = 'Đã gửi yêu cầu hủy. Yêu cầu đang chờ admin xác nhận hoàn tiền.';
                } else {
                    $_SESSION['booking_error'] = 'Không thể gửi yêu cầu. Chỉ đơn đã xác nhận, chưa gửi yêu cầu và chưa đến giờ chơi mới được yêu cầu hủy.';
                }
            }

            header('Location: ' . APP_BASE_URL . '/booking/mine');
            exit;
        }

        $userEmail = $_SESSION['user_email'] ?? '';
        $myBookings = $this->bookingModel->getBookingsForUser($userId, $userEmail);
        $csrfToken = $this->csrfToken();
        $this->render('client/my-bookings', compact('myBookings', 'csrfToken', 'success', 'error'));
    }

    public function webhook($id = null)
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->respondToWebhook(405, false);
        }

        $secret = getenv('SEPAY_WEBHOOK_SECRET');
        if ($secret === false || $secret === '') {
            error_log('SePay webhook rejected: SEPAY_WEBHOOK_SECRET is not configured.');
            $this->respondToWebhook(503, false);
        }

        $rawBody = file_get_contents('php://input');
        $timestamp = (int) ($_SERVER['HTTP_X_SEPAY_TIMESTAMP'] ?? 0);
        $signature = (string) ($_SERVER['HTTP_X_SEPAY_SIGNATURE'] ?? '');
        if ($timestamp <= 0 || abs(time() - $timestamp) > 300) {
            $this->respondToWebhook(401, false);
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $rawBody, $secret);
        if ($signature === '' || !hash_equals($expectedSignature, $signature)) {
            $this->respondToWebhook(401, false);
        }

        $data = json_decode($rawBody, true);
        if (!is_array($data) || !isset($data['id'], $data['content'], $data['transferType'], $data['transferAmount'])) {
            $this->respondToWebhook(400, false);
        }

        if ($data['transferType'] !== 'in') {
            $this->respondToWebhook(200, true);
        }

        if (!preg_match('/\bDATSAN\s*(\d+)\b/i', (string) $data['content'], $matches)) {
            error_log('SePay webhook ignored: incoming transfer has no booking reference.');
            $this->respondToWebhook(200, true);
        }

        $bookingId = (int) $matches[1];
        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking) {
            error_log('SePay webhook ignored: booking ' . $bookingId . ' was not found.');
            $this->respondToWebhook(200, true);
        }

        if (abs((float) $data['transferAmount'] - (float) $booking['total_amount']) > 0.01) {
            error_log('SePay webhook ignored: amount mismatch for booking ' . $bookingId . '.');
            $this->respondToWebhook(200, true);
        }

        if ($booking['status'] === 'confirmed') {
            $this->respondToWebhook(200, true);
        }

        if ($booking['status'] !== 'pending') {
            error_log('SePay webhook requires manual review for booking ' . $bookingId . ' with status ' . $booking['status'] . '.');
            $this->respondToWebhook(200, true);
        }

        if (!$this->bookingModel->confirmPendingBooking($bookingId)) {
            $booking = $this->bookingModel->getBookingById($bookingId);
            if (!$booking || $booking['status'] !== 'confirmed') {
                error_log('SePay webhook could not confirm booking ' . $bookingId . '.');
                $this->respondToWebhook(500, false);
            }
            $this->respondToWebhook(200, true);
        }

        $booking['status'] = 'confirmed';
        if (empty($booking['email_sent'])) {
            $this->sendSuccessEmail($booking);
            $this->bookingModel->markEmailSent($bookingId);
        }

        $this->respondToWebhook(200, true);
    }

    private function respondToWebhook($statusCode, $success)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
    }

    public function checkSlotsApi($courtId, $courtNumber, $date)
    {
        header('Content-Type: application/json');
        if ($courtId <= 0 || $courtNumber < 1 || $courtNumber > 5) {
            http_response_code(400);
            echo json_encode(['error' => 'Sân không hợp lệ']);
            exit;
        }
        // Tự động hủy các đơn quá 3 phút chưa thanh toán trước khi trả về danh sách slot
        $this->bookingModel->cancelExpiredBookings();

        $bookedSlots = $this->bookingModel->getBookedSlotsByDate($courtId, $courtNumber, $date);
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

    private function sendSuccessEmail($booking)
    {
        try {
            $user = $this->userModel->getUserById($booking['user_id']);
            $userName = $user['fullname'] ?? $user['name'] ?? $user['username'] ?? 'Khách hàng';

            $court = $this->courtModel->getCourtById($booking['court_id']);
            $courtName = ($court['name'] ?? 'Sân cầu lông') . ' - Sân ' . (int) ($booking['court_number'] ?? 1);

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
            error_log('Unable to send booking invoice email: ' . $e->getMessage());
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
        $courtNumber = filter_var($postData['court_number'] ?? null, FILTER_VALIDATE_INT);
        $date = $postData['booking_date'] ?? '';
        $slots = $postData['slots'] ?? [];
        $selectedServices = $postData['services'] ?? [];
        $customerEmail = trim($postData['customer_email'] ?? '');

        if (empty($date) || empty($slots) || !is_array($slots)) {
            return ['error' => 'Vui lòng chọn ít nhất một khung giờ đặt sân!'];
        }

        if ($courtNumber === false || $courtNumber < 1 || $courtNumber > 5) {
            return ['error' => 'Vui lòng chọn sân số từ 1 đến 5!'];
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

            if ($this->bookingModel->isSlotOverlapped($courtId, $courtNumber, $date, $slotStart, $slotEnd)) {
                return ['error' => "Khung giờ {$slotLabel} đã có người đặt, vui lòng chọn lại!"];
            }
        }

        // Tính tổng tiền
        $court = $this->courtModel->getCourtById($courtId);
        if (!$court || ($court['status'] ?? 'active') !== 'active') {
            return ['error' => 'Sân này hiện không hoạt động và chưa thể nhận đặt sân.'];
        }
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
        $bookingId = $this->bookingModel->createBookingWithDetails($userId, $courtId, $courtNumber, $date, $startTimeStr, $endTimeStr, $totalMoney, $customerEmail);

        if ($bookingId) {
            $bankId = "MB";
            $accountNo = "0339936910";
            $accountName = "PHAM MINH VU";

            $desc = "DATSAN " . $bookingId;
            $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.png?amount={$totalMoney}&addInfo=" . urlencode($desc) . "&accountName=" . urlencode($accountName);

            return [
                'success' => true,
                'booking_id' => $bookingId,
                'court_number' => $courtNumber,
                'qr_url' => $qrUrl,
                'email' => $customerEmail
            ];
        }

        return ['error' => 'Lỗi hệ thống, không thể đặt sân!'];
    }
}
