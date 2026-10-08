<?php
namespace App\Models;

use App\Core\Database;

class Booking {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Kiểm tra trùng lịch
    public function isSlotOverlapped($courtId, $date, $startTime, $endTime) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total FROM bookings 
            WHERE court_id = ? AND booking_date = ? AND status != 'cancelled' 
            AND NOT (end_time <= ? OR start_time >= ?)
        ");
        $stmt->execute([$courtId, $date, $startTime, $endTime]);
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }

    // Lấy danh sách khung giờ đã bị đặt (phục vụ Ajax)
    public function getBookedSlots($courtId, $date) {
        $stmt = $this->db->prepare("
            SELECT start_time, end_time FROM bookings 
            WHERE court_id = ? AND booking_date = ? AND status != 'cancelled'
        ");
        $stmt->execute([$courtId, $date]);
        return $stmt->fetchAll();
    }

    // Tạo đơn đặt mới
    public function createBooking($userId, $courtId, $date, $startTime, $endTime) {
        $stmt = $this->db->prepare("
            INSERT INTO bookings (user_id, court_id, booking_date, start_time, end_time, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        if ($stmt->execute([$userId, $courtId, $date, $startTime, $endTime])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Duyệt hoặc Hủy đơn
    public function updateStatus($bookingId, $status) {
        $stmt = $this->db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $bookingId]);
    }
}