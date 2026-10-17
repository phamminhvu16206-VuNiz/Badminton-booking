<?php
// File: admin/index.php
// Chức năng: Trang quản trị dành cho Admin để xem và duyệt các đơn đặt sân

session_start();
require_once '../config/database.php';

// Kiểm tra quyền Admin (Yêu cầu phải đăng nhập và role là admin)
$user_role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id']) || $user_role !== 'admin') {
    $_SESSION['error'] = 'Bạn không có quyền truy cập vào trang quản trị!';
    header('Location: ../login.php');
    exit();
}

$success = '';
$error = '';

// Xử lý thay đổi trạng thái đơn đặt sân (Duyệt hoặc Hủy)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $booking_id = (int)$_GET['id'];

    try {
        if ($action === 'confirm') {
            $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
            if ($stmt->execute([$booking_id])) {
                $success = 'Đã duyệt đơn đặt sân thành công!';
            } else {
                $error = 'Không thể duyệt đơn, vui lòng thử lại.';
            }
        } elseif ($action === 'cancel') {
            $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            if ($stmt->execute([$booking_id])) {
                $success = 'Đã hủy đơn đặt sân thành công!';
            } else {
                $error = 'Không thể hủy đơn, vui lòng thử lại.';
            }
        }
    } catch (PDOException $e) {
        $error = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
    }
}

// Truy vấn lấy danh sách tất cả các đơn đặt sân kèm thông tin user và sân
try {
    $stmt = $conn->prepare("
        SELECT b.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
               c.name AS court_name
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN courts c ON b.court_id = c.id 
        ORDER BY b.id DESC
    ");
    $stmt->execute();
    $all_bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_bookings = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Trị Admin - Badminton Booking</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #1e293b; color: white; }
        .sidebar a { color: #94a3b8; text-decoration: none; padding: 12px 20px; display: block; border-radius: 6px; margin-bottom: 5px; transition: 0.2s; }
        .sidebar a:hover, .sidebar a.active { background-color: #0f172a; color: white; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 sidebar p-4">
            <h4 class="fw-bold text-white mb-4"><i class="fa-solid fa-shield-halved me-2 text-success"></i>Admin Panel</h4>
            <div class="mb-4 text-muted small">Xin chào, <strong class="text-white"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></strong></div>
            
            <nav class="nav flex-column">
                <a href="index.php" class="active"><i class="fa-solid fa-calendar-days me-2"></i>Quản lý đặt sân</a>
                <a href="courts.php"><i class="fa-solid fa-layer-group me-2"></i>Quản lý sân & giá</a>
                <a href="services.php"><i class="fa-solid fa-concierge-bell me-2"></i>Quản lý dịch vụ</a>
                <a href="users.php"><i class="fa-solid fa-users me-2"></i>Quản lý người dùng</a>
                <a href="../index.php" target="_blank"><i class="fa-solid fa-globe me-2"></i>Xem trang web</a>
                <a href="../logout.php" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a>
            </nav>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark">Quản Lý Danh Sách Đặt Sân</h3>
                    <p class="text-muted small mb-0">Theo dõi và xét duyệt lịch đặt sân từ khách hàng</p>
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
                <?php if (!empty($all_bookings)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#ID</th>
                                    <th>Khách hàng</th>
                                    <th>Sân</th>
                                    <th>Ngày chơi</th>
                                    <th>Khung giờ</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_bookings as $b): ?>
                                    <tr>
                                        <td><strong>#<?= $b['id'] ?></strong></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($b['user_name'] ?? '') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($b['user_phone'] ?? '') ?> - <?= htmlspecialchars($b['user_email'] ?? '') ?></small>
                                        </td>
                                        <td><span class="fw-semibold text-success"><?= htmlspecialchars($b['court_name'] ?? '') ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($b['booking_date'])) ?></td>
                                        <td><?= date('H:i', strtotime($b['start_time'])) ?> - <?= date('H:i', strtotime($b['end_time'])) ?></td>
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
                                                <a href="index.php?action=confirm&id=<?= $b['id'] ?>" class="btn btn-success btn-sm fw-bold me-1" title="Duyệt đơn">
                                                    <i class="fa-solid fa-check"></i> Duyệt
                                                </a>
                                                <a href="index.php?action=cancel&id=<?= $b['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn này không?');" title="Hủy đơn">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">Đã xử lý</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-folder-open fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có đơn đặt sân nào trong hệ thống!</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>