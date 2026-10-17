<?php
// File: my-bookings.php
// Chức năng: Hiển thị lịch sử các đơn đặt sân của người dùng đang đăng nhập

require_once 'config/database.php';
require_once 'includes/header.php';

$userId = $_SESSION['user_id'] ?? 0;
$userEmail = $_SESSION['user_email'] ?? '';

// Truy vấn lấy danh sách đơn đặt sân theo user_id HOẶC customer_email
try {
    $stmt = $conn->prepare("
        SELECT b.*, c.name AS court_name, c.image AS court_image, c.address AS court_address 
        FROM bookings b
        LEFT JOIN courts c ON b.court_id = c.id
        WHERE (b.user_id = ? AND ? > 0) OR (b.customer_email = ? AND ? != '')
        ORDER BY b.id DESC
    ");
    $stmt->execute([$userId, $userId, $userEmail, $userEmail]);
    $myBookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $myBookings = [];
}
?>

<div class="container my-4">
    <!-- TIÊU ĐỀ TRANG -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-custom-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch Sử Đặt Sân</h2>
            <p class="text-muted">Quản lý danh sách các sân cầu lông bạn đã đặt và theo dõi trạng thái thanh toán</p>
            <div class="mx-auto bg-success mb-4" style="width: 80px; height: 3px;"></div>
        </div>
    </div>

    <?php if (empty($myBookings)): ?>
        <!-- KHUNG TRỐNG KHI CHƯA CÓ LỊCH SỬ ĐẶT SÂN -->
        <div class="col-12 text-center py-5">
            <div class="card card-custom p-5 bg-white shadow-sm" style="max-width: 700px; margin: 0 auto;">
                <i class="fa-solid fa-calendar-xmark fs-1 text-muted mb-3"></i>
                <h5 class="text-dark fw-bold mb-2">Bạn chưa có lịch sử đặt sân nào!</h5>
                <p class="text-muted small mb-4">Hãy chọn cho mình một sân ưng ý và tiến hành đặt lịch ngay nhé.</p>
                <div>
                    <a href="courts.php" class="btn btn-custom-primary btn-sm px-4 py-2 fw-bold">
                        <i class="fa-solid fa-list me-1"></i> Xem danh sách sân
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- DANH SÁCH HIỂN THỊ LỊCH SỬ ĐẶT SÂN (LƯỚI 2 CỘT) -->
        <div class="row g-4">
            <?php foreach ($myBookings as $booking): ?>
                <div class="col-md-6">
                    <div class="card card-custom h-100 bg-white p-3 shadow-sm border-0 rounded-4">
                        <div class="card-body d-flex flex-column">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark fs-6">Mã đơn: #<?= $booking['id'] ?></span>
                                <?php
                                $statusClass = 'bg-warning text-dark';
                                $statusText = 'Đang chờ thanh toán';
                                if ($booking['status'] === 'confirmed') {
                                    $statusClass = 'bg-success text-white';
                                    $statusText = 'Đã xác nhận (Thành công)';
                                } elseif ($booking['status'] === 'cancelled') {
                                    $statusClass = 'bg-danger text-white';
                                    $statusText = 'Đã hủy';
                                }
                                ?>
                                <span class="badge <?= $statusClass ?> px-3 py-2 rounded-pill small fw-semibold">
                                    <?= $statusText ?>
                                </span>
                            </div>

                            <hr class="text-muted opacity-25 my-2">

                            <h5 class="card-title fw-bold text-dark mb-2">
                                <i class="fa-solid fa-location-dot text-danger me-2"></i>
                                <?= htmlspecialchars($booking['court_name'] ?? 'Sân Cầu Lông') ?>
                            </h5>

                            <p class="text-muted small mb-2">
                                <i class="fa-regular fa-calendar me-2 text-success"></i>Ngày đặt: <strong><?= $booking['booking_date'] ?></strong>
                            </p>

                            <p class="text-muted small mb-2">
                                <i class="fa-regular fa-clock me-2 text-success"></i>Khung giờ: <strong><?= $booking['start_time'] ?> - <?= $booking['end_time'] ?></strong>
                            </p>

                            <p class="text-muted small mb-3">
                                <i class="fa-solid fa-money-bill-wave me-2 text-success"></i>Tổng tiền: <strong class="text-success fs-6"><?= number_format($booking['total_amount'] ?? 0, 0, ',', '.') ?> VNĐ</strong>
                            </p>

                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <small class="text-muted">Email nhận hóa đơn:</small>
                                <span class="small fw-semibold text-dark"><?= htmlspecialchars($booking['customer_email']) ?></span>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>