<?php
// File: my-bookings.php
// Chức năng: Hiển thị lịch sử đặt sân của người dùng hiện tại và cho phép hủy đơn đặt sân (nếu còn trạng thái pending)

require_once 'config/database.php';
require_once 'includes/header.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_required'] = 'Vui lòng đăng nhập để xem lịch sử đặt sân!';
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Xử lý yêu cầu hủy đơn đặt sân
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    try {
        // Kiểm tra xem đơn đặt sân này có thuộc về user hiện tại và đang ở trạng thái 'pending' không
        $stmt_check = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
        $stmt_check->execute([$booking_id, $user_id]);
        $booking = $stmt_check->fetch();

        if ($booking) {
            if ($booking['status'] === 'pending') {
                $stmt_cancel = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
                if ($stmt_cancel->execute([$booking_id])) {
                    $success = 'Đã hủy đơn đặt sân thành công!';
                } else {
                    $error = 'Không thể hủy đơn đặt sân, vui lòng thử lại.';
                }
            } else {
                $error = 'Chỉ có thể hủy các đơn đặt sân đang ở trạng thái chờ duyệt (Pending)!';
            }
        } else {
            $error = 'Không tìm thấy đơn đặt sân hoặc bạn không có quyền hủy.';
        }
    } catch (PDOException $e) {
        $error = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
    }
}

// Truy vấn danh sách lịch sử đặt sân của user
try {
    $stmt = $conn->prepare("
        SELECT b.*, c.name AS court_name, c.address AS court_address, 
               ts.start_time, ts.end_time 
        FROM bookings b 
        JOIN courts c ON b.court_id = c.id 
        JOIN time_slots ts ON b.time_slot_id = ts.id 
        WHERE b.user_id = ? 
        ORDER BY b.id DESC
    ");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $bookings = [];
}
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-custom-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch Sử Đặt Sân</h2>
            <p class="text-muted">Quản lý danh sách các sân cầu lông bạn đã đặt</p>
            <div class="mx-auto bg-success mb-4" style="width: 80px; height: 3px;"></div>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-custom p-4 bg-white">
        <?php if (!empty($bookings)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Tên sân</th>
                            <th>Ngày chơi</th>
                            <th>Khung giờ</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><strong>#<?= $b['id'] ?></strong></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($b['court_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($b['court_address']) ?></small>
                                </td>
                                <td><i class="fa-regular fa-calendar me-1 text-secondary"></i><?= date('d/m/Y', strtotime($b['booking_date'])) ?></td>
                                <td><i class="fa-regular fa-clock me-1 text-secondary"></i><?= date('H:i', strtotime($b['start_time'])) ?> - <?= date('H:i', strtotime($b['end_time'])) ?></td>
                                <td class="text-success fw-bold"><?= number_format($b['total_price'], 0, ',', '.') ?> VNĐ</td>
                                <td>
                                    <?php if ($b['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                    <?php elseif ($b['status'] === 'confirmed'): ?>
                                        <span class="badge bg-success">Đã xác nhận</span>
                                    <?php elseif ($b['status'] === 'cancelled'): ?>
                                        <span class="badge bg-secondary">Đã hủy</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($b['status'] === 'pending'): ?>
                                        <a href="my-bookings.php?action=cancel&id=<?= $b['id'] ?>" 
                                           class="btn btn-outline-danger btn-sm" 
                                           onclick="return confirm('Bạn có chắc chắn muốn hủy đơn đặt sân này không?');">
                                            <i class="fa-solid fa-xmark me-1"></i>Hủy lịch
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-light btn-sm text-muted" disabled>Không thể hủy</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-calendar-xmark fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">Bạn chưa có lịch sử đặt sân nào!</h5>
                <p class="text-muted small">Hãy chọn cho mình một sân ưng ý và tiến hành đặt lịch ngay nhé.</p>
                <a href="courts.php" class="btn btn-custom-primary btn-sm mt-2">Xem danh sách sân</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>