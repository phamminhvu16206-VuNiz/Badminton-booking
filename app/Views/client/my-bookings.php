<?php require_once APP_ROOT . '/app/Views/layouts/header.php'; ?>

<style>
    .modal-backdrop.show {
        background-color: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(5px);
        opacity: 1;
    }

    .booking-detail-modal .modal-content {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 1rem 3rem rgba(15, 23, 42, 0.25);
    }
</style>

<div class="container my-4">
    <!-- TIÊU ĐỀ TRANG -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-custom-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch Sử Đặt Sân</h2>
            <p class="text-muted">Quản lý danh sách các sân cầu lông bạn đã đặt và theo dõi trạng thái thanh toán</p>
            <div class="mx-auto bg-success mb-4" style="width: 80px; height: 3px;"></div>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (empty($myBookings)): ?>
        <!-- KHUNG TRỐNG KHI CHƯA CÓ LỊCH SỬ ĐẶT SÂN -->
        <div class="col-12 text-center py-5">
            <div class="card card-custom p-5 bg-white shadow-sm" style="max-width: 700px; margin: 0 auto;">
                <i class="fa-solid fa-calendar-xmark fs-1 text-muted mb-3"></i>
                <h5 class="text-dark fw-bold mb-2">Bạn chưa có lịch sử đặt sân nào!</h5>
                <p class="text-muted small mb-4">Hãy chọn cho mình một sân ưng ý và tiến hành đặt lịch ngay nhé.</p>
                <div>
                    <a href="home/courts" class="btn btn-custom-primary btn-sm px-4 py-2 fw-bold">
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
                                if (($booking['cancellation_status'] ?? 'none') === 'pending') {
                                    $statusClass = 'bg-info text-dark';
                                    $statusText = 'Chờ xác nhận hủy / hoàn tiền';
                                } elseif (($booking['cancellation_status'] ?? 'none') === 'refunded') {
                                    $statusClass = 'bg-secondary text-white';
                                    $statusText = 'Đã hoàn tiền';
                                } elseif ($booking['status'] === 'confirmed') {
                                    $statusClass = 'bg-success text-white';
                                    $statusText = 'Đã xác nhận (Thành công)';
                                } elseif ($booking['status'] === 'cancelled') {
                                    $statusClass = 'bg-danger text-white';
                                    $statusText = 'Đã hủy';
                                }
                                ?>
                                <span class="badge <?= $statusClass ?> px-3 py-2 rounded-pill small fw-semibold">
                                    <?= htmlspecialchars($statusText, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>

                            <hr class="text-muted opacity-25 my-2">

                            <h5 class="card-title fw-bold text-dark mb-2">
                                <i class="fa-solid fa-location-dot text-danger me-2"></i>
                                <?= htmlspecialchars($booking['court_name'] ?? 'Sân Cầu Lông') ?>
                                <span class="badge bg-primary ms-1">Sân <?= (int) ($booking['court_number'] ?? 1) ?></span>
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
                            <div class="mt-3 text-end">
                                <button type="button" class="btn btn-outline-success btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#bookingDetail<?= (int) $booking['id'] ?>">
                                    <i class="fa-solid fa-eye me-1"></i> Xem chi tiết
                                </button>
                                <?php if (
                                    $booking['status'] === 'confirmed'
                                    && ($booking['cancellation_status'] ?? 'none') === 'none'
                                    && !empty($booking['can_request_cancellation'])
                                ): ?>
                                    <button type="button" class="btn btn-outline-danger btn-sm fw-semibold ms-1" data-bs-toggle="modal" data-bs-target="#cancelBooking<?= (int) $booking['id'] ?>">
                                        <i class="fa-solid fa-ban me-1"></i> Yêu cầu hủy
                                    </button>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal fade booking-detail-modal" id="bookingDetail<?= (int) $booking['id'] ?>" tabindex="-1" aria-labelledby="bookingDetailTitle<?= (int) $booking['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold" id="bookingDetailTitle<?= (int) $booking['id'] ?>">Chi tiết đơn #<?= (int) $booking['id'] ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                            </div>
                            <div class="modal-body">
                                <dl class="row mb-0">
                                    <dt class="col-5">Sân</dt><dd class="col-7"><?= htmlspecialchars($booking['court_name'] ?? 'Sân cầu lông') ?></dd>
                                    <dt class="col-5">Sân số mấy</dt><dd class="col-7">Sân <?= (int) ($booking['court_number'] ?? 1) ?></dd>
                                    <dt class="col-5">Địa chỉ</dt><dd class="col-7"><?= htmlspecialchars($booking['court_address'] ?? 'Chưa có địa chỉ') ?></dd>
                                    <dt class="col-5">Ngày chơi</dt><dd class="col-7"><?= htmlspecialchars($booking['booking_date']) ?></dd>
                                    <dt class="col-5">Khung giờ</dt><dd class="col-7"><?= htmlspecialchars($booking['start_time'] . ' - ' . $booking['end_time']) ?></dd>
                                    <dt class="col-5">Tổng tiền</dt><dd class="col-7"><?= number_format($booking['total_amount'] ?? 0, 0, ',', '.') ?> VNĐ</dd>
                                    <dt class="col-5">Email hóa đơn</dt><dd class="col-7"><?= htmlspecialchars($booking['customer_email'] ?? '') ?></dd>
                                    <dt class="col-5">Trạng thái</dt><dd class="col-7"><?= htmlspecialchars($statusText) ?></dd>
                                    <?php if (!empty($booking['cancellation_reason'])): ?>
                                        <dt class="col-5">Lý do hủy</dt><dd class="col-7"><?= nl2br(htmlspecialchars($booking['cancellation_reason'], ENT_QUOTES, 'UTF-8')) ?></dd>
                                    <?php endif; ?>
                                    <?php if (!empty($booking['cancellation_bank_account_number'])): ?>
                                        <dt class="col-5">Ngân hàng hoàn tiền</dt><dd class="col-7"><?= htmlspecialchars($booking['cancellation_bank_name'], ENT_QUOTES, 'UTF-8') ?></dd>
                                        <dt class="col-5">Tên chủ tài khoản</dt><dd class="col-7"><?= htmlspecialchars($booking['cancellation_bank_account_name'], ENT_QUOTES, 'UTF-8') ?></dd>
                                        <dt class="col-5">Số tài khoản</dt><dd class="col-7"><?= htmlspecialchars($booking['cancellation_bank_account_number'], ENT_QUOTES, 'UTF-8') ?></dd>
                                    <?php endif; ?>
                                </dl>
                                <div class="text-center mt-4 pt-3 border-top">
                                    <h6 class="fw-bold mb-2">Mã QR đơn đặt sân</h6>
                                    <img
                                        src="<?= htmlspecialchars(\App\Core\BookingQr::imageUrl($booking['id']), ENT_QUOTES, 'UTF-8') ?>"
                                        alt="Mã QR đơn đặt sân #<?= (int) $booking['id'] ?>"
                                        width="220"
                                        height="220"
                                        class="img-fluid border rounded-3 p-2"
                                        loading="lazy">
                                    <div class="small text-muted mt-2"><?= htmlspecialchars(\App\Core\BookingQr::bookingCode($booking['id']), ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (
                    $booking['status'] === 'confirmed'
                    && ($booking['cancellation_status'] ?? 'none') === 'none'
                    && !empty($booking['can_request_cancellation'])
                ): ?>
                    <div class="modal fade booking-detail-modal" id="cancelBooking<?= (int) $booking['id'] ?>" tabindex="-1" aria-labelledby="cancelBookingTitle<?= (int) $booking['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="booking/mine">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold" id="cancelBookingTitle<?= (int) $booking['id'] ?>">Yêu cầu hủy đơn #<?= (int) $booking['id'] ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted">Yêu cầu sẽ được gửi tới admin. Admin sẽ kiểm tra và hoàn tiền thủ công trước khi xác nhận hủy.</p>
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                        <label class="form-label fw-semibold" for="cancellationReason<?= (int) $booking['id'] ?>">Lý do hủy</label>
                                        <textarea class="form-control" id="cancellationReason<?= (int) $booking['id'] ?>" name="cancellation_reason" rows="4" maxlength="1000" required></textarea>
                                        <hr>
                                        <h6 class="fw-bold">Thông tin tài khoản nhận hoàn tiền</h6>
                                        <div class="mb-3">
                                            <label class="form-label" for="cancellationBank<?= (int) $booking['id'] ?>">Ngân hàng</label>
                                            <input class="form-control" type="text" id="cancellationBank<?= (int) $booking['id'] ?>" name="cancellation_bank_name" maxlength="100" autocomplete="organization" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="cancellationAccountName<?= (int) $booking['id'] ?>">Tên chủ tài khoản</label>
                                            <input class="form-control" type="text" id="cancellationAccountName<?= (int) $booking['id'] ?>" name="cancellation_bank_account_name" maxlength="150" autocomplete="name" required>
                                        </div>
                                        <div>
                                            <label class="form-label" for="cancellationAccountNumber<?= (int) $booking['id'] ?>">Số tài khoản</label>
                                            <input class="form-control" type="text" id="cancellationAccountNumber<?= (int) $booking['id'] ?>" name="cancellation_bank_account_number" maxlength="50" inputmode="numeric" autocomplete="off" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                                        <button type="submit" class="btn btn-danger" name="request_cancellation" value="1">Gửi yêu cầu hủy</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once APP_ROOT . '/app/Views/layouts/footer.php';
?>