<?php

namespace App\Models;

class Booking extends BaseModel
{
    // Tự động hủy các đơn quá 3 phút mà chưa thanh toán (status = 'pending')
    public function cancelExpiredBookings()
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $stmt = $this->db->prepare("
            UPDATE bookings 
            SET status = 'cancelled' 
            WHERE status = 'pending' 
            AND created_at < (NOW() - INTERVAL 3 MINUTE)
        ");
        $stmt->execute();
    }

    public function isSlotOverlapped($courtId, $courtNumber, $date, $startTime, $endTime)
    {
        $this->cancelExpiredBookings(); // Dọn dẹp trước khi check trùng
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total FROM bookings 
            WHERE court_id = ? AND court_number = ? AND booking_date = ? AND status != 'cancelled'
            AND NOT (end_time <= ? OR start_time >= ?)
        ");
        $stmt->execute([$courtId, $courtNumber, $date, $startTime, $endTime]);
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }

    public function getBookedSlotsByDate($courtId, $courtNumber, $date)
    {
        $this->cancelExpiredBookings(); // Dọn dẹp trước khi lấy slot
        $stmt = $this->db->prepare("
            SELECT start_time, end_time FROM bookings 
            WHERE court_id = ? AND court_number = ? AND booking_date = ? AND status != 'cancelled'
        ");
        $stmt->execute([$courtId, $courtNumber, $date]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $bookedLabels = [];
        foreach ($rows as $row) {
            $start = strtotime($row['start_time']);
            $end = strtotime($row['end_time']);
            while ($start < $end) {
                $bookedLabels[] = date('H:i', $start);
                $start = strtotime('+30 minutes', $start);
            }
        }
        return array_unique($bookedLabels);
    }

    public function getBookingById($bookingId)
    {
        $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Tạo đơn với đầy đủ thông tin tổng tiền và email
    public function createBookingWithDetails($userId, $courtId, $courtNumber, $date, $startTime, $endTime, $totalMoney, $customerEmail)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $stmt = $this->db->prepare("
            INSERT INTO bookings (user_id, court_id, court_number, booking_date, start_time, end_time, total_amount, customer_email, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        if ($stmt->execute([$userId, $courtId, $courtNumber, $date, $startTime, $endTime, $totalMoney, $customerEmail])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function markEmailSent($bookingId)
    {
        $stmt = $this->db->prepare("UPDATE bookings SET email_sent = 1 WHERE id = ?");
        return $stmt->execute([$bookingId]);
    }

    public function updateStatus($bookingId, $status)
    {
        $stmt = $this->db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $bookingId]);
    }

    public function confirmPendingBooking($bookingId)
    {
        $stmt = $this->db->prepare("
            UPDATE bookings
            SET status = 'confirmed'
            WHERE id = ? AND status = 'pending'
        ");
        $stmt->execute([$bookingId]);
        return $stmt->rowCount() === 1;
    }

    public function getAllBookings()
    {
        return $this->db->query("
            SELECT b.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
                   c.name AS court_name,
                   b.court_number
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN courts c ON b.court_id = c.id
            ORDER BY b.id DESC
        ")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getBookingsForUser($userId, $email)
    {
        return $this->statement("
            SELECT b.*, c.name AS court_name, c.image AS court_image, c.address AS court_address,
                   (b.user_id = ? AND (b.booking_date > CURDATE() OR (b.booking_date = CURDATE() AND b.start_time > CURTIME()))) AS can_request_cancellation
            FROM bookings b
            LEFT JOIN courts c ON b.court_id = c.id
            WHERE (b.user_id = ? AND ? > 0) OR (b.customer_email = ? AND ? != '')
            ORDER BY b.id DESC
        ", [$userId, $userId, $userId, $email, $email])->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function requestCancellation($bookingId, $userId, $reason, $bankName, $bankAccountName, $bankAccountNumber)
    {
        $stmt = $this->db->prepare("
            UPDATE bookings
            SET cancellation_status = 'pending',
                cancellation_reason = ?,
                cancellation_bank_name = ?,
                cancellation_bank_account_name = ?,
                cancellation_bank_account_number = ?,
                cancellation_requested_at = NOW()
            WHERE id = ?
              AND user_id = ?
              AND status = 'confirmed'
              AND cancellation_status = 'none'
              AND (
                  booking_date > CURDATE()
                  OR (booking_date = CURDATE() AND start_time > CURTIME())
              )
        ");
        $stmt->execute([$reason, $bankName, $bankAccountName, $bankAccountNumber, $bookingId, $userId]);

        return $stmt->rowCount() === 1;
    }

    public function getCancellationRequests()
    {
        return $this->db->query("
            SELECT b.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
                   c.name AS court_name, c.address AS court_address
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN courts c ON b.court_id = c.id
            WHERE b.cancellation_status IN ('pending', 'refunded')
            ORDER BY (b.cancellation_status = 'pending') DESC, b.cancellation_requested_at DESC
        ")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function confirmCancellationRefund($bookingId)
    {
        $stmt = $this->db->prepare("
            UPDATE bookings
            SET status = 'cancelled',
                cancellation_status = 'refunded',
                refunded_at = NOW()
            WHERE id = ?
              AND status = 'confirmed'
              AND cancellation_status = 'pending'
        ");
        $stmt->execute([$bookingId]);

        return $stmt->rowCount() === 1;
    }
}
