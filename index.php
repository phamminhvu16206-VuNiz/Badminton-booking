<?php
// File: index.php
// Chức năng: Trang chủ hiển thị banner, danh sách sân nổi bật, giới thiệu và ưu điểm hệ thống

require_once 'config/database.php';
require_once 'includes/header.php';

// Truy vấn lấy danh sách 6 sân đang hoạt động từ cơ sở dữ liệu
try {
    $stmt = $conn->prepare("SELECT * FROM courts WHERE status = 'active' LIMIT 6");
    $stmt->execute();
    $courts = $stmt->fetchAll();
} catch (PDOException $e) {
    $courts = [];
}
?>

<!-- Banner Website -->
<div class="container my-4">
    <div class="position-relative rounded-4 overflow-hidden shadow-sm">
        <img src="assets/images/banner-badminton.jpg" alt="Banner Sân Cầu Lông" class="w-100" style="height: 380px; object-fit: cover;">
        <div class="position-absolute top-50 start-50 translate-middle text-center text-white w-100 px-3">
            <h1 class="display-5 fw-bold text-uppercase mb-2" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">ĐẶT SÂN CẦU LÔNG ONLINE</h1>
            <p class="fs-5 mb-3" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.7);">Nền tảng đặt sân nhanh chóng - tiện lợi - dễ dàng</p>
            <a href="courts.php" class="btn btn-success btn-lg fw-bold px-4 py-2 rounded-3 shadow">
                <i class="fa-solid fa-calendar-check me-2"></i>Đặt sân ngay
            </a>
        </div>
    </div>
</div>

<div class="container">
    <!-- PHẦN SÂN NỔI BẬT -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-custom-primary"><i class="fa-solid fa-fire me-2"></i>Danh Sách Sân Nổi Bật</h2>
            <p class="text-muted">Lựa chọn sân tập phù hợp với nhu cầu của bạn</p>
            <div class="mx-auto bg-success mb-4" style="width: 80px; height: 3px;"></div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <?php if (!empty($courts)): ?>
            <?php foreach ($courts as $court): ?>
                <div class="col-md-4">
                    <div class="card card-custom h-100">
                        <!-- Hiển thị hình ảnh (nếu file ảnh không có sẵn, hiển thị ảnh mặc định từ Unsplash minh họa) -->
                        <img src="assets/images/<?= htmlspecialchars($court['image']) ?>"
                            class="card-img-top"
                            alt="<?= htmlspecialchars($court['name']) ?>"
                            style="height: 200px; object-fit: cover;"
                            onerror="this.src='https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=500&auto=format&fit=crop&q=60'">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($court['name']) ?></h5>
                            <p class="card-text text-muted small mb-2"><i class="fa-solid fa-location-dot text-danger me-1"></i><?= htmlspecialchars($court['address']) ?></p>
                            <p class="card-text text-success fw-bold mb-3">
                                <i class="fa-solid fa-tag me-1"></i><?= number_format($court['price'], 0, ',', '.') ?> VNĐ / giờ
                            </p>
                            <div class="mt-auto d-flex gap-2">
                                <a href="court-detail.php?id=<?= $court['id'] ?>" class="btn btn-outline-success btn-sm w-50">
                                    <i class="fa-solid fa-circle-info me-1"></i>Xem chi tiết
                                </a>
                                <a href="booking.php?court_id=<?= $court['id'] ?>" class="btn btn-custom-primary btn-sm w-50">
                                    <i class="fa-solid fa-bolt me-1"></i>Đặt sân
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted py-4">
                <p>Hiện chưa có sân cầu lông nào trong hệ thống.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mb-5">
        <a href="courts.php" class="btn btn-outline-dark px-4 py-2">
            Xem tất cả các sân <i class="fa-solid fa-arrow-right ms-2"></i>
        </a>
    </div>

    <!-- PHẦN GIỚI THIỆU -->
    <div class="container my-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
            <div class="row align-items-center g-4">

                <!-- Cột văn bản giới thiệu -->
                <div class="col-lg-7">
                    <h2 class="fw-bold text-success mb-3">Về Badminton Booking</h2>
                    <p class="text-muted mb-3" style="line-height: 1.7;">
                        Chúng tôi cung cấp hệ thống đặt sân cầu lông trực tuyến tiên tiến nhất, kết nối người chơi với các cụm sân chất lượng cao trên toàn quốc. Không còn cảnh gọi điện đặt lịch thủ công hay lo lắng về việc hết sân giờ cao điểm.
                    </p>
                    <p class="text-muted mb-0" style="line-height: 1.7;">
                        Giao diện thân thiện, quy trình đặt sân đơn giản trong vài cú click chuột và lịch sử quản lý minh bạch giúp bạn hoàn toàn yên tâm trải nghiệm.
                    </p>
                </div>

                <!-- Cột hình ảnh minh họa -->
                <div class="col-lg-5 text-center">
                    <img src="assets/images/about-badminton.jpg"
                        alt="Sân cầu lông"
                        class="img-fluid rounded-4 shadow-sm w-100"
                        style="max-height: 280px; object-fit: cover;"
                        onerror="this.src='https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=600&auto=format&fit=crop&q=60'">
                </div>

            </div>
        </div>
    </div>

    <!-- PHẦN ƯU ĐIỂM -->
    <div class="row mb-5 text-center">
        <div class="col-12 mb-4">
            <h2 class="fw-bold text-custom-primary">Ưu Điểm Nổi Bật</h2>
            <p class="text-muted">Tại sao hàng ngàn người chơi lựa chọn chúng tôi?</p>
            <div class="mx-auto bg-success mb-4" style="width: 80px; height: 3px;"></div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card card-custom p-4 h-100 text-center">
                <div class="fs-1 text-success mb-3"><i class="fa-solid fa-bolt"></i></div>
                <h5 class="fw-bold">Đặt sân online nhanh chóng</h5>
                <p class="text-muted small">Thao tác chọn sân, chọn giờ và xác nhận đặt lịch diễn ra chưa đầy 1 phút.</p>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card card-custom p-4 h-100 text-center">
                <div class="fs-1 text-success mb-3"><i class="fa-solid fa-calendar-days"></i></div>
                <h5 class="fw-bold">Kiểm tra lịch trống trực quan</h5>
                <p class="text-muted small">Dễ dàng theo dõi các khung giờ còn trống để chủ động sắp xếp thời gian.</p>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card card-custom p-4 h-100 text-center">
                <div class="fs-1 text-success mb-3"><i class="fa-solid fa-wallet"></i></div>
                <h5 class="fw-bold">Giá cả minh bạch rõ ràng</h5>
                <p class="text-muted small">Mức giá niêm yết công khai, hệ thống tự động tính toán tổng tiền chính xác.</p>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card card-custom p-4 h-100 text-center">
                <div class="fs-1 text-success mb-3"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <h5 class="fw-bold">Quản lý lịch đặt thông minh</h5>
                <p class="text-muted small">Xem lại toàn bộ lịch sử đơn hàng, trạng thái xác nhận và chủ động hủy lịch khi cần thiết.</p>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card card-custom p-4 h-100 text-center">
                <div class="fs-1 text-success mb-3"><i class="fa-solid fa-headset"></i></div>
                <h5 class="fw-bold">Hỗ trợ khách hàng tận tâm</h5>
                <p class="text-muted small">Đội ngũ hỗ trợ trực tuyến sẵn sàng giải đáp thắc mắc và xử lý sự cố nhanh chóng.</p>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>