<?php

namespace App\Models;

use App\Core\Database;

class Booking
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

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

    public function isSlotOverlapped($courtId, $date, $startTime, $endTime)
    {
        $this->cancelExpiredBookings(); // Dọn dẹp trước khi check trùng
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total FROM bookings 
            WHERE court_id = ? AND booking_date = ? AND status != 'cancelled' 
            AND NOT (end_time <= ? OR start_time >= ?)
        ");
        $stmt->execute([$courtId, $date, $startTime, $endTime]);
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }

    public function getBookedSlotsByDate($courtId, $date)
    {
        $this->cancelExpiredBookings(); // Dọn dẹp trước khi lấy slot
        $stmt = $this->db->prepare("
            SELECT start_time, end_time FROM bookings 
            WHERE court_id = ? AND booking_date = ? AND status != 'cancelled'
        ");
        $stmt->execute([$courtId, $date]);
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
    public function createBookingWithDetails($userId, $courtId, $date, $startTime, $endTime, $totalMoney, $customerEmail)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $stmt = $this->db->prepare("
            INSERT INTO bookings (user_id, court_id, booking_date, start_time, end_time, total_amount, customer_email, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        if ($stmt->execute([$userId, $courtId, $date, $startTime, $endTime, $totalMoney, $customerEmail])) {
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
}
