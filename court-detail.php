<?php
// File: court-detail.php - Trang hiển thị chi tiết thông tin sân
session_start();
require_once __DIR__ . '/config/database.php';

$court_id = intval($_GET['id'] ?? 0);

try {
    $stmt = $conn->prepare("SELECT * FROM courts WHERE id = ?");
    $stmt->execute([$court_id]);
    $court = $stmt->fetch();

    if (!$court) {
        header('Location: courts.php');
        exit();
    }
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row g-4">
        <!-- Cột trái: Hình ảnh sân -->
        <div class="col-md-6">
            <div class="position-relative rounded-4 overflow-hidden shadow-sm">
                <img src="assets/images/<?= htmlspecialchars($court['image'] ?? 'san-1.jpg') ?>"
                    class="img-fluid w-100 rounded-4"
                    alt="<?= htmlspecialchars($court['name'] ?? 'Sân cầu lông') ?>"
                    style="height: 380px; object-fit: cover;"
                    onerror="this.src='https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=800&auto=format&fit=crop&q=60'">

                <!-- Nhãn trạng thái trên ảnh -->
                <span class="position-absolute top-0 end-0 m-3 badge <?= ($court['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-danger' ?> fs-6 px-3 py-2 rounded-pill shadow">
                    <?= ($court['status'] ?? 'active') === 'active' ? 'Sân tiêu chuẩn' : 'Bảo trì' ?>
                </span>
            </div>
        </div>

        <!-- Cột phải: Thông tin & Giá -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge bg-success mb-2 px-3 py-2">Sân tiêu chuẩn</span>
                        <h2 class="fw-bold text-dark mb-3"><?= htmlspecialchars($court['name']) ?></h2>

                        <div class="mb-3">
                            <span class="text-muted fs-6">Giá thuê chỉ từ:</span>
                            <div class="fs-2 fw-bold text-danger">
                                <?= number_format($court['price_per_hour'] ?? $court['price'] ?? 150000) ?> VNĐ <small class="fs-6 text-muted">/ giờ</small>
                            </div>
                        </div>

                        <hr>

                        <!-- Mô tả & Tiện ích sân -->
                        <h5 class="fw-bold fs-6 mb-2"><i class="fa-solid fa-info-circle me-2 text-success"></i>Thông tin mô tả:</h5>
                        <p class="text-muted">
                            <?= !empty($court['description']) ? nl2br(htmlspecialchars($court['description'])) : 'Sân cầu lông đạt chuẩn thi đấu, thảm PVC chống trượt cao cấp, hệ thống đèn chiếu sáng chống chói mắt hiện đại.' ?>
                        </p>

                        <h5 class="fw-bold fs-6 mb-2 mt-4"><i class="fa-solid fa-list-check me-2 text-success"></i>Tiện ích đi kèm:</h5>
                        <ul class="list-unstyled text-muted row g-2">
                            <li class="col-6"><i class="fa-solid fa-wifi text-primary me-2"></i> Wifi miễn phí</li>
                            <li class="col-6"><i class="fa-solid fa-square-parking text-warning me-2"></i> Bãi đỗ xe rộng rãi</li>
                            <li class="col-6"><i class="fa-solid fa-fan text-info me-2"></i> Quạt quạt công suất lớn</li>
                            <li class="col-6"><i class="fa-solid fa-bottle-water text-success me-2"></i> Căng tin nước uống</li>
                        </ul>
                    </div>

                    <!-- Nút sang trang Đặt sân -->
                    <div class="mt-4 pt-3 border-top">
                        <a href="booking.php?court_id=<?= $court['id'] ?>" class="btn btn-success btn-lg w-100 fw-bold py-3 text-uppercase shadow-sm">
                            <i class="fa-solid fa-calendar-check me-2"></i> Tiến Hành Đặt Sân
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>