<?php
// File: booking.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/autoload.php';

use App\Controllers\BookingController;
use App\Models\Court;
use App\Models\Service;

$bookingController = new BookingController();
$courtModel = new Court();
$serviceModel = new Service();

// 1. API AJAX check slot đã đặt & check payment
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'check_slots') {
        $courtId = intval($_GET['court_id'] ?? 0);
        $date = $_GET['date'] ?? date('Y-m-d');
        $bookingController->checkSlotsApi($courtId, $date);
    }
    if ($_GET['action'] === 'check_payment') {
        $bookingId = intval($_GET['booking_id'] ?? 0);
        $bookingController->checkPaymentStatusApi($bookingId);
    }
}

// Lấy thông tin sân và danh sách dịch vụ từ CSDL
$courtId = intval($_GET['id'] ?? 1);
$court = $courtModel->getCourtById($courtId);
$services = $serviceModel->getAllServices(); // Lấy từ DB hoặc dùng mảng mặc định bên dưới nếu CSDL trống

// Dữ liệu dịch vụ dự phòng nếu chưa có trong DB
if (empty($services)) {
    $services = [
        ['id' => 1, 'name' => 'Thuê vợt cầu lông', 'price' => 30000, 'unit' => 'giờ'],
        ['id' => 2, 'name' => 'Mua 1 ống cầu lông', 'price' => 25000, 'unit' => 'ống'],
        ['id' => 3, 'name' => 'Nước suối (2 chai)', 'price' => 10000, 'unit' => 'lần']
    ];
}

$bookingResult = null;
if (isset($_POST['btn_submit_booking'])) {
    $userId = $_SESSION['user_id'] ?? 0;
    $bookingResult = $bookingController->processBooking($_POST, $userId);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đặt Sân - Badminton Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .slot-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }

        @media (max-width: 768px) {
            .slot-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .slot-btn {
            padding: 8px 4px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            background-color: #ffffff;
            color: #333;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .slot-btn:hover:not(.disabled) {
            border-color: #198754;
            color: #198754;
        }

        .slot-btn.selected {
            background-color: #198754 !important;
            color: #ffffff !important;
            border-color: #198754 !important;
        }

        .slot-btn.booked {
            background-color: #dc3545 !important;
            color: #ffffff !important;
            border-color: #dc3545 !important;
            cursor: not-allowed;
            opacity: 0.85;
        }

        .slot-btn.disabled {
            background-color: #ced4da !important;
            color: #6c757d !important;
            cursor: not-allowed;
            border-color: #dee2e6 !important;
        }

        .legend-box {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 3px;
            vertical-align: middle;
            margin-right: 4px;
        }

        .court-img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            border-radius: 12px;
        }

        .btn-custom-submit {
            background-color: #52ab80;
            border: none;
            color: #fff;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .btn-custom-submit:hover {
            background-color: #3e8e67;
            color: #fff;
        }

        /* Custom kiểu Dịch vụ bổ sung giống hình ảnh */
        .service-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 16px;
        }

        .service-item {
            padding: 8px 0;
        }

        .service-item:not(:last-child) {
            border-bottom: 1px dashed #e0e0e0;
        }

        .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }
    </style>
</head>

<body class="py-4">

    <div class="container" style="max-width: 1100px;">

        <div class="mb-3">
            <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách sân
            </a>
        </div>

        <!-- Màn hình Thanh toán VietQR & Thông báo thành công -->
        <?php if (!empty($bookingResult) && isset($bookingResult['success'])): ?>
            <div id="paymentPendingBox" class="alert alert-warning text-center py-4 rounded-4 shadow-sm mb-4">
                <h4 class="fw-bold text-dark mb-2">
                    <i class="fa-solid fa-spinner fa-spin text-warning me-2"></i>Đang Chờ Thanh Toán Qua Mã VietQR...
                </h4>
                <p class="text-muted mb-3">Vui lòng quét mã bên dưới. Trạng thái sẽ tự động cập nhật ngay khi ngân hàng nhận tiền!</p>
                <div class="bg-white p-3 d-inline-block rounded-3 shadow-sm mb-3 border">
                    <img src="<?= $bookingResult['qr_url'] ?>" alt="Mã VietQR" class="img-fluid" style="max-width: 250px;">
                </div>
                <div class="badge bg-dark fs-6 px-3 py-2 d-block mx-auto" style="max-width: 280px;">
                    Nội dung CK: DATSAN <?= $bookingResult['booking_id'] ?>
                </div>
            </div>

            <div id="paymentSuccessBox" class="alert alert-success text-center py-5 rounded-4 shadow-sm mb-4 d-none">
                <i class="fa-solid fa-circle-check text-success display-1 mb-3"></i>
                <h3 class="fw-bold text-success mb-2">ĐẶT SÂN THÀNH CÔNG!</h3>
                <p class="fs-5 text-dark">Mã đơn đặt sân: <strong>#<?= $bookingResult['booking_id'] ?></strong></p>
                <p class="text-muted mb-4">Hóa đơn điện tử đã được gửi về email: <strong><?= htmlspecialchars($bookingResult['email'] ?? '') ?></strong></p>
                <a href="index.php" class="btn btn-success fw-bold px-4 py-2">Về Trang Chủ</a>
            </div>

            <script>
                const bookingId = <?= $bookingResult['booking_id'] ?>;
                const checkInterval = setInterval(() => {
                    fetch(`booking.php?action=check_payment&booking_id=${bookingId}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'confirmed') {
                                clearInterval(checkInterval);
                                document.getElementById('paymentPendingBox').classList.add('d-none');
                                document.getElementById('paymentSuccessBox').classList.remove('d-none');
                            }
                        });
                }, 2500);
            </script>
        <?php endif; ?>

        <!-- KHUNG ĐẶT LỊCH GIAO DIỆN 2 CỘT -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <form method="POST" id="bookingForm">
                <input type="hidden" name="court_id" value="<?= $court['id'] ?? 1 ?>">
                <input type="hidden" name="start_hour" id="start_hour" value="">
                <input type="hidden" name="end_hour" id="end_hour" value="">

                <div class="row g-4">

                    <!-- CỘT TRÁI: THÔNG TIN SÂN -->
                    <div class="col-lg-5 col-md-6 border-end-lg">
                        <img src="<?= !empty($court['image']) ? $court['image'] : 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=600' ?>"
                            alt="Sân cầu lông" class="court-img mb-3">

                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($court['name'] ?? 'Sân Cầu Lông Tiêu Chuẩn A1') ?></h4>
                            <span class="badge bg-success rounded-pill px-3 py-2">Sân Tiêu Chuẩn</span>
                        </div>

                        <p class="text-muted small mb-3">
                            <i class="fa-solid fa-location-dot text-danger me-1"></i>
                            <?= htmlspecialchars($court['address'] ?? '123 Đường Số 1, Quận 10, TP.HCM') ?>
                        </p>

                        <hr class="text-muted opacity-25">

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Giá Thuê / Giờ</small>
                                <span class="fs-4 fw-bold text-success">
                                    <?= number_format($court['price_per_hour'] ?? 150000) ?> VNĐ
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Trạng Thái Sân</small>
                                <span class="badge bg-primary px-3 py-2">Hoạt động</span>
                            </div>
                        </div>
                    </div>

                    <!-- CỘT PHẢI: CHỌN KHUNG GIỜ, DỊCH VỤ VÀ ĐẶT SÂN -->
                    <div class="col-lg-7 col-md-6">
                        <h5 class="fw-bold text-dark mb-3">
                            <i class="fa-regular fa-calendar-check text-success me-2"></i>Chọn Khung Giờ Đặt Sân
                        </h5>

                        <!-- 1. Ngày đặt sân -->
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Ngày đặt sân:</label>
                            <input type="date" name="booking_date" id="booking_date"
                                class="form-control rounded-3"
                                value="<?= date('Y-m-d') ?>"
                                min="<?= date('Y-m-d') ?>">
                        </div>

                        <!-- 2. Chú thích trạng thái -->
                        <div class="d-flex flex-wrap gap-3 small text-muted mb-3">
                            <div><span class="legend-box border bg-white"></span>Trống</div>
                            <div><span class="legend-box bg-success"></span>Đang chọn</div>
                            <div><span class="legend-box bg-danger"></span>Đã đặt</div>
                            <div><span class="legend-box bg-secondary opacity-50"></span>Đã qua</div>
                        </div>

                        <!-- 3. Lưới Khung giờ -->
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <div class="slot-grid" id="slotContainer">
                                <!-- Render bằng Javascript -->
                            </div>
                        </div>

                        <!-- 4. DỊCH VỤ BỔ SUNG (TÙY CHỌN) - GIỐNG HÌNH ẢNH MẪU -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark mb-2">Dịch vụ bổ sung (Tùy chọn):</label>
                            <div class="service-box">
                                <?php foreach ($services as $srv): ?>
                                    <div class="d-flex justify-content-between align-items-center service-item">
                                        <div class="form-check">
                                            <input class="form-check-input service-checkbox"
                                                type="checkbox"
                                                name="services[]"
                                                value="<?= $srv['id'] ?>"
                                                id="service_<?= $srv['id'] ?>"
                                                data-price="<?= $srv['price'] ?>">
                                            <label class="form-check-label text-dark fw-normal cursor-pointer" for="service_<?= $srv['id'] ?>">
                                                <?= htmlspecialchars($srv['name']) ?>
                                            </label>
                                        </div>
                                        <span class="text-secondary small fw-semibold">
                                            +<?= number_format($srv['price']) ?> VNĐ / <?= htmlspecialchars($srv['unit'] ?? 'lần') ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- 5. Email nhận hóa đơn -->
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Email nhận hóa đơn:</label>
                            <input type="email" name="customer_email" class="form-control rounded-3"
                                placeholder="example@gmail.com"
                                value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" required>
                        </div>

                        <!-- 6. Ghi chú thêm -->
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Ghi chú thêm:</label>
                            <input type="text" name="note" class="form-control rounded-3" placeholder="Mượn lưới, nước uống...">
                        </div>

                        <!-- 7. Nút xác nhận đặt sân -->
                        <button type="submit" name="btn_submit_booking" id="btnSubmit" class="btn btn-custom-submit w-100 py-2 fs-6 rounded-3">
                            <i class="fa-solid fa-circle-check me-2"></i>XÁC NHẬN ĐẶT SÂN
                        </button>
                    </div>

                </div>
            </form>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const courtId = <?= $court['id'] ?? 1 ?>;
            const dateInput = document.getElementById('booking_date');
            const slotContainer = document.getElementById('slotContainer');
            const startHourInput = document.getElementById('start_hour');
            const endHourInput = document.getElementById('end_hour');

            let selectedStart = null;
            let selectedEnd = null;

            function loadSlots() {
                const selectedDate = dateInput.value;
                fetch(`booking.php?action=check_slots&court_id=${courtId}&date=${selectedDate}`)
                    .then(res => res.json())
                    .then(bookedSlots => renderSlots(bookedSlots))
                    .catch(() => renderSlots([]));
            }

            function renderSlots(bookedSlots) {
                slotContainer.innerHTML = '';
                selectedStart = null;
                selectedEnd = null;
                startHourInput.value = '';
                endHourInput.value = '';

                const now = new Date();
                const todayStr = now.toISOString().split('T')[0];
                const currentHour = now.getHours();
                const isToday = (dateInput.value === todayStr);

                for (let h = 5; h <= 22; h++) {
                    const startStr = (h < 10 ? '0' + h : h) + ':00';
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'slot-btn';
                    btn.innerText = startStr;
                    btn.dataset.hour = h;

                    if (isToday && h <= currentHour) {
                        btn.classList.add('disabled');
                    } else if (bookedSlots.includes(h) || bookedSlots.includes(startStr)) {
                        btn.classList.add('booked');
                    } else {
                        btn.addEventListener('click', () => handleSlotClick(h));
                    }

                    slotContainer.appendChild(btn);
                }
            }

            function handleSlotClick(hour) {
                const buttons = document.querySelectorAll('.slot-btn:not(.booked):not(.disabled)');

                if (selectedStart === null || selectedEnd !== null) {
                    selectedStart = hour;
                    selectedEnd = null;
                } else if (hour > selectedStart) {
                    selectedEnd = hour;
                } else {
                    selectedStart = hour;
                    selectedEnd = null;
                }

                buttons.forEach(btn => {
                    const h = parseInt(btn.dataset.hour);
                    if (selectedEnd === null) {
                        if (h === selectedStart) btn.classList.add('selected');
                        else btn.classList.remove('selected');
                    } else {
                        if (h >= selectedStart && h <= selectedEnd) btn.classList.add('selected');
                        else btn.classList.remove('selected');
                    }
                });

                startHourInput.value = selectedStart;
                endHourInput.value = (selectedEnd !== null) ? selectedEnd + 1 : selectedStart + 1;
            }

            dateInput.addEventListener('change', loadSlots);
            loadSlots();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>