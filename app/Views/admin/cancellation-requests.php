<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= htmlspecialchars((defined('APP_BASE_URL') ? APP_BASE_URL : '') . '/admin/', ENT_QUOTES, 'UTF-8') ?>">
    <title>Yêu Cầu Hủy / Hoàn Tiền - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <aside class="col-md-3 col-lg-2 sidebar p-4">
            <h4 class="fw-bold text-white mb-4"><i class="fa-solid fa-shield-halved me-2 text-success"></i>Admin Panel</h4>
            <div class="mb-4 text-muted small">Xin chào, <strong class="text-white"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></strong></div>
            <nav class="nav flex-column">
                <a href="bookings"><i class="fa-solid fa-calendar-days me-2"></i>Quản lý đặt sân</a>
                <a href="cancellation-requests" class="active"><i class="fa-solid fa-rotate-left me-2"></i>Yêu cầu hủy / hoàn tiền</a>
                <a href="courts"><i class="fa-solid fa-layer-group me-2"></i>Quản lý sân & giá</a>
                <a href="services"><i class="fa-solid fa-concierge-bell me-2"></i>Quản lý dịch vụ</a>
                <a href="users"><i class="fa-solid fa-users me-2"></i>Quản lý người dùng</a>
                <a href="../home/index" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-globe me-2"></i>Xem trang web</a>
                <a href="../auth/logout" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a>
            </nav>
        </aside>
        <main class="col-md-9 col-lg-10 p-4">
            <div class="mb-4">
                <h3 class="fw-bold text-dark">Yêu Cầu Hủy / Hoàn Tiền</h3>
                <p class="text-muted small mb-0">Đối soát và hoàn tiền thủ công trước khi xác nhận hủy đơn.</p>
            </div>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="card card-custom p-4 bg-white">
                <?php if (!empty($requests)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Sân</th>
                                    <th>Ngày / giờ</th>
                                    <th>Số tiền cần hoàn</th>
                                    <th>Lý do hủy</th>
                                    <th>Thông tin nhận hoàn tiền</th>
                                    <th>Trạng thái yêu cầu</th>
                                    <th class="text-center">Xử lý</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><strong>#<?= (int) $request['id'] ?></strong></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($request['user_name'] ?? '') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($request['user_email'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($request['court_name'] ?? 'Sân cầu lông') ?>
                                            <span class="badge bg-primary">Sân <?= (int) ($request['court_number'] ?? 1) ?></span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($request['booking_date']) ?><br>
                                            <small><?= htmlspecialchars(substr($request['start_time'], 0, 5) . ' - ' . substr($request['end_time'], 0, 5)) ?></small>
                                        </td>
                                        <td class="fw-bold text-danger"><?= number_format((float) ($request['total_amount'] ?? 0), 0, ',', '.') ?> VNĐ</td>
                                        <td class="text-break" style="min-width: 180px;"><?= nl2br(htmlspecialchars($request['cancellation_reason'] ?? '', ENT_QUOTES, 'UTF-8')) ?></td>
                                        <td class="text-break" style="min-width: 200px;">
                                            <div><strong>Ngân hàng:</strong> <?= htmlspecialchars($request['cancellation_bank_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                            <div><strong>Chủ TK:</strong> <?= htmlspecialchars($request['cancellation_bank_account_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                            <div><strong>Số TK:</strong> <?= htmlspecialchars($request['cancellation_bank_account_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td>
                                            <?php if ($request['cancellation_status'] === 'pending'): ?>
                                                <span class="badge bg-warning text-dark">Chờ admin hoàn tiền</span>
                                                <small class="d-block text-muted mt-1"><?= htmlspecialchars($request['cancellation_requested_at'] ?? '') ?></small>
                                            <?php else: ?>
                                                <span class="badge bg-success">Đã hoàn tiền / đã hủy</span>
                                                <small class="d-block text-muted mt-1"><?= htmlspecialchars($request['refunded_at'] ?? '') ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($request['cancellation_status'] === 'pending'): ?>
                                                <form method="POST" onsubmit="return confirm('Chỉ xác nhận sau khi đã hoàn tiền thủ công cho khách. Tiếp tục?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="booking_id" value="<?= (int) $request['id'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm fw-bold" name="confirm_refund" value="1">
                                                        <i class="fa-solid fa-check me-1"></i>Đã hoàn tiền
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small">Đã xử lý</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="small text-muted">Sau khi hoàn tiền qua ngân hàng/ví ngoài hệ thống, bấm “Đã hoàn tiền” để chuyển đơn sang đã hủy.</div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-inbox fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có yêu cầu hủy sân nào.</h5>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
