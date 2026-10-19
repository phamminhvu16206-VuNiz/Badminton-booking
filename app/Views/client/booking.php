<?php require_once APP_ROOT . '/app/Views/layouts/header.php'; ?>

<style>
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

    .slot-btn:hover:not(.disabled):not(.booked) {
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

    .price-summary-box {
        background-color: #e8f5e9;
        border: 1px dashed #198754;
        border-radius: 10px;
        padding: 15px;
    }
</style>

<div class="container my-4" style="max-width: 1050px;">

    <div class="mb-3">
        <a href="home/courts" class="btn btn-sm btn-outline-secondary rounded-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách sân
        </a>
    </div>

    <!-- MÀN HÌNH MODAL POPUP VIETQR & ĐẾM NGƯỢC 3 PHÚT -->
    <?php if (!empty($bookingResult) && isset($bookingResult['success'])): ?>
        <div class="modal fade show d-block" id="vietqrModal" tabindex="-1" style="background: rgba(0, 0, 0, 0.65); backdrop-filter: blur(5px); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

                    <div class="modal-header bg-success text-white border-0 py-3">
                        <h5 class="modal-title fw-bold text-center w-100" id="modalTitle">
                            <i class="fa-solid fa-qrcode me-2"></i>Thanh Toán Đơn Đặt Sân
                        </h5>
                    </div>

                    <div class="modal-body text-center p-4" id="paymentPendingBox">
                        <div class="alert alert-warning py-2 px-3 mb-3 rounded-pill fw-bold text-dark d-inline-block">
                            <i class="fa-solid fa-clock me-1 text-danger"></i> Thời gian giữ sân: <span id="countdownTimer" class="text-danger">03:00</span>
                        </div>

                        <p class="text-muted small mb-3">Vui lòng quét mã bên dưới. Hệ thống sẽ tự động xác nhận và gửi hóa đơn vào email ngay khi nhận tiền!</p>

                        <div class="bg-light p-3 d-inline-block rounded-3 shadow-sm mb-3 border">
                            <img src="<?= $bookingResult['qr_url'] ?>" alt="Mã VietQR" class="img-fluid" style="max-width: 240px;">
                        </div>

                        <div class="bg-dark text-white p-2 rounded-3 small fw-bold mb-2">
                            Nội dung CK: <span class="text-warning">DATSAN <?= $bookingResult['booking_id'] ?></span>
                        </div>
                    </div>

                    <div class="modal-body text-center p-5 d-none" id="paymentSuccessBox">
                        <i class="fa-solid fa-circle-check text-success display-1 mb-3"></i>
                        <h3 class="fw-bold text-success mb-2">ĐẶT SÂN THÀNH CÔNG!</h3>
                        <p class="fs-6 text-dark mb-1">Mã đơn đặt sân: <strong>#<?= $bookingResult['booking_id'] ?></strong></p>
                        <p class="text-muted small mb-1">Sân số: <strong><?= (int) $bookingResult['court_number'] ?></strong></p>
                        <p class="text-muted small mb-4">Hóa đơn điện tử đã được gửi về email: <br><strong><?= htmlspecialchars($bookingResult['email'] ?? '') ?></strong></p>
                        <a href="booking/mine" class="btn btn-success fw-bold px-4 py-2 rounded-3">Xem Lịch Sử Đặt Sân</a>
                    </div>

                    <div class="modal-body text-center p-5 d-none" id="paymentExpiredBox">
                        <i class="fa-solid fa-triangle-exclamation text-danger display-1 mb-3"></i>
                        <h3 class="fw-bold text-danger mb-2">HẾT THỜI GIAN GIỮ SÂN!</h3>
                        <p class="text-muted small mb-4">Đơn đặt sân đã tự động hủy do quá 3 phút chưa thanh toán.</p>
                        <a href="booking/index?court_id=<?= (int) $court['id'] ?>" class="btn btn-danger fw-bold px-4 py-2 rounded-3">Chọn Lại Sân</a>
                    </div>

                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const bookingId = <?= $bookingResult['booking_id'] ?>;
                let timeLeft = 180;
                const timerElement = document.getElementById('countdownTimer');

                const countdownInterval = setInterval(() => {
                    let minutes = Math.floor(timeLeft / 60);
                    let seconds = timeLeft % 60;
                    let minStr = minutes < 10 ? '0' + minutes : minutes;
                    let secStr = seconds < 10 ? '0' + seconds : seconds;
                    timerElement.innerText = `${minStr}:${secStr}`;

                    if (timeLeft <= 0) {
                        clearInterval(countdownInterval);
                        clearInterval(checkInterval);
                        document.getElementById('paymentPendingBox').classList.add('d-none');
                        document.getElementById('paymentExpiredBox').classList.remove('d-none');
                        document.getElementById('modalTitle').innerText = 'Đã Hết Hạn';
                    }
                    timeLeft--;
                }, 1000);

                const checkInterval = setInterval(() => {
                    fetch(`booking/index?action=check_payment&booking_id=${bookingId}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'confirmed') {
                                clearInterval(checkInterval);
                                clearInterval(countdownInterval);
                                document.getElementById('modalTitle').innerText = 'Xác Nhận Thành Công';
                                document.getElementById('paymentPendingBox').classList.add('d-none');
                                document.getElementById('paymentSuccessBox').classList.remove('d-none');
                            } else if (data.status === 'expired') {
                                clearInterval(checkInterval);
                                clearInterval(countdownInterval);
                                document.getElementById('paymentPendingBox').classList.add('d-none');
                                document.getElementById('paymentExpiredBox').classList.remove('d-none');
                                document.getElementById('modalTitle').innerText = 'Đã Hết Hạn';
                            }
                        });
                }, 2500);
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($bookingResult['error'])): ?>
        <div class="alert alert-danger text-center fw-bold py-2 mb-3 rounded-3 shadow-sm">
            <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $bookingResult['error'] ?>
        </div>
    <?php endif; ?>

    <!-- KHUNG ĐẶT SÂN TỔNG THỂ (BỐ CỤC 1 CỘT DỌC) -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">

        <!-- PHẦN 1: THÔNG TIN SÂN (NẰM TRÊN CÙNG) -->
        <div class="row align-items-center g-4 pb-4 border-bottom mb-4">
            <div class="col-md-5">
                <div class="rounded-4 overflow-hidden shadow-sm">
                    <img src="assets/images/<?= htmlspecialchars(!empty($court['image']) ? $court['image'] : 'san-1.jpg') ?>"
                        class="img-fluid w-100 rounded-4"
                        alt="<?= htmlspecialchars($court['name'] ?? 'Sân cầu lông') ?>"
                        style="height: 220px; object-fit: cover;"
                        onerror="this.src='https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&auto=format&fit=crop&q=60'">
                </div>
            </div>
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <h3 class="fw-bold text-dark mb-0"><?= htmlspecialchars($court['name'] ?? 'Sân Cầu Lông Số 1') ?></h3>
                    <span class="badge bg-success rounded-pill px-3 py-2 small">Sân Tiêu Chuẩn</span>
                </div>

                <p class="text-muted small mb-3">
                    <i class="fa-solid fa-location-dot text-danger me-1"></i>
                    <?= htmlspecialchars($court['address'] ?? '123 Đường Nguyễn Văn Linh, Quận 7, TP.HCM') ?>
                </p>

                <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-3">
                    <div>
                        <span class="text-muted small d-block">Giá Thuê / Giờ</span>
                        <strong class="text-success fs-4" id="displayPricePerHour" data-price="<?= $pricePerHour ?>">
                            <?= number_format($pricePerHour, 0, ',', '.') ?> VNĐ
                        </strong>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Trạng Thái Sân</span>
                        <span class="badge bg-primary px-3 py-2 rounded-pill">Hoạt động</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PHẦN 2: CHỌN KHUNG GIỜ VÀ DỊCH VỤ (NẰM BÊN DƯỚI) -->
        <form method="POST" id="bookingForm">
            <input type="hidden" name="court_id" value="<?= $court['id'] ?? 1 ?>">
            <div id="selectedSlotsContainer"></div>

            <div class="mb-4">
                <label for="court_number" class="form-label fw-bold text-dark">
                    <i class="fa-solid fa-table-tennis-paddle-ball me-2 text-success"></i>Chọn sân con
                </label>
                <select class="form-select form-select-lg rounded-3" id="court_number" name="court_number" required>
                    <?php for ($courtNumber = 1; $courtNumber <= 5; $courtNumber++): ?>
                        <option value="<?= $courtNumber ?>">Sân <?= $courtNumber ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Ngày đặt sân -->
            <div class="mb-4">
                <h5 class="fw-bold text-success mb-3">
                    <i class="fa-regular fa-calendar-days me-2"></i>Chọn Khung Giờ Đặt Sân (Chọn nhiều ô)
                </h5>
                <label for="booking_date" class="form-label fw-semibold small text-muted">Ngày đặt sân:</label>
                <input type="date" class="form-control form-control-lg rounded-3" id="booking_date" name="booking_date" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
            </div>

            <!-- Chú thích màu sắc -->
            <div class="d-flex flex-wrap gap-3 mb-3 small text-muted">
                <div><span class="legend-box border bg-white"></span>Trống</div>
                <div><span class="legend-box bg-success"></span>Đang chọn</div>
                <div><span class="legend-box bg-danger"></span>Đã đặt</div>
                <div><span class="legend-box bg-secondary opacity-50"></span>Đã qua</div>
            </div>

            <!-- Lưới chọn khung giờ -->
            <div class="p-3 bg-light rounded-4 mb-4 border">
                <div class="slot-grid" id="slotContainer">
                    <!-- Render bằng Javascript -->
                </div>
            </div>

            <!-- Dịch vụ bổ sung -->
            <div class="mb-4">
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
                                <label class="form-check-label text-dark fw-normal" for="service_<?= $srv['id'] ?>">
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

            <!-- Bảng tính tiền tạm tính -->
            <div class="price-summary-box mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-success fw-bold d-block"><i class="fa-solid fa-receipt me-1"></i>Tổng Tiền Tạm Tính:</span>
                    <small class="text-muted" id="selectedSummaryText">Chưa chọn khung giờ nào</small>
                </div>
                <h3 class="fw-bold text-success mb-0" id="totalPriceDisplay">0 VNĐ</h3>
            </div>

            <!-- Email nhận hóa đơn -->
            <div class="mb-3">
                <label class="form-label small text-muted mb-1">Email nhận hóa đơn:</label>
                <input type="email" name="customer_email" class="form-control rounded-3"
                    placeholder="example@gmail.com"
                    value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" required>
            </div>

            <!-- Ghi chú thêm -->
            <div class="mb-4">
                <label class="form-label small text-muted mb-1">Ghi chú thêm:</label>
                <input type="text" name="note" class="form-control rounded-3" placeholder="Mượn lưới, nước uống...">
            </div>

            <!-- Nút xác nhận đặt sân -->
            <button type="submit" name="btn_submit_booking" id="btnSubmit" class="btn btn-custom-submit btn-lg w-100 py-3 rounded-3 shadow">
                <i class="fa-solid fa-circle-check me-2"></i>XÁC NHẬN ĐẶT SÂN
            </button>
        </form>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const courtId = <?= $court['id'] ?? 1 ?>;
        const dateInput = document.getElementById('booking_date');
        const courtNumberInput = document.getElementById('court_number');
        const slotContainer = document.getElementById('slotContainer');
        const selectedSlotsContainer = document.getElementById('selectedSlotsContainer');
        const bookingForm = document.getElementById('bookingForm');
        const totalPriceDisplay = document.getElementById('totalPriceDisplay');
        const selectedSummaryText = document.getElementById('selectedSummaryText');
        const pricePerHour = parseFloat(document.getElementById('displayPricePerHour').dataset.price) || 150000;

        let selectedSlots = [];
        let slotsList = [];

        function generate30MinSlots() {
            slotsList = [];
            for (let h = 5; h <= 22; h++) {
                const hStr = h < 10 ? '0' + h : h;
                slotsList.push({
                    hour: h,
                    minute: 0,
                    label: `${hStr}:00`
                });
                slotsList.push({
                    hour: h,
                    minute: 30,
                    label: `${hStr}:30`
                });
            }
        }

        function loadSlots() {
            const selectedDate = dateInput.value;
            fetch(`booking/index?action=check_slots&court_id=${courtId}&court_number=${courtNumberInput.value}&date=${selectedDate}`)
                .then(res => res.json())
                .then(bookedSlots => renderSlots(bookedSlots))
                .catch(() => renderSlots([]));
        }

        function renderSlots(bookedSlots) {
            slotContainer.innerHTML = '';
            selectedSlots = [];
            updateHiddenInputs();
            calculateTotalPrice();

            const now = new Date();
            const todayStr = now.toISOString().split('T')[0];
            const currentHour = now.getHours();
            const currentMinute = now.getMinutes();
            const isToday = (dateInput.value === todayStr);

            slotsList.forEach(slot => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'slot-btn';
                btn.innerText = slot.label;
                btn.dataset.label = slot.label;

                const isPast = isToday && (slot.hour < currentHour || (slot.hour === currentHour && slot.minute <= currentMinute));
                const isBooked = bookedSlots.includes(slot.label);

                if (isPast) {
                    btn.classList.add('disabled');
                } else if (isBooked) {
                    btn.classList.add('booked');
                } else {
                    btn.addEventListener('click', () => toggleSlot(btn, slot.label));
                }

                slotContainer.appendChild(btn);
            });
        }

        function toggleSlot(btn, label) {
            if (btn.classList.contains('selected')) {
                btn.classList.remove('selected');
                selectedSlots = selectedSlots.filter(item => item !== label);
            } else {
                btn.classList.add('selected');
                if (!selectedSlots.includes(label)) {
                    selectedSlots.push(label);
                }
            }
            updateHiddenInputs();
            calculateTotalPrice();
        }

        function updateHiddenInputs() {
            selectedSlotsContainer.innerHTML = '';
            selectedSlots.forEach(slot => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'slots[]';
                input.value = slot;
                selectedSlotsContainer.appendChild(input);
            });
        }

        function calculateTotalPrice() {
            const slotCount = selectedSlots.length;
            const courtTotal = (pricePerHour / 2) * slotCount;

            let serviceTotal = 0;
            const checkboxes = document.querySelectorAll('.service-checkbox:checked');
            checkboxes.forEach(cb => {
                serviceTotal += parseFloat(cb.dataset.price) || 0;
            });

            const grandTotal = courtTotal + serviceTotal;
            totalPriceDisplay.innerText = grandTotal.toLocaleString('vi-VN') + ' VNĐ';

            if (slotCount > 0) {
                selectedSummaryText.innerText = `${slotCount} khung giờ (30 phút/ô) + Dịch vụ chọn thêm`;
            } else {
                selectedSummaryText.innerText = 'Chưa chọn khung giờ nào';
            }
        }

        document.querySelectorAll('.service-checkbox').forEach(cb => {
            cb.addEventListener('change', calculateTotalPrice);
        });

        bookingForm.addEventListener('submit', function(e) {
            if (selectedSlots.length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một khung giờ đặt sân!');
                return false;
            }
        });

        dateInput.addEventListener('change', loadSlots);
        courtNumberInput.addEventListener('change', loadSlots);
        generate30MinSlots();
        loadSlots();
    });
</script>

<?php
// Nhúng Footer chung
require_once APP_ROOT . '/app/Views/layouts/footer.php';
?>