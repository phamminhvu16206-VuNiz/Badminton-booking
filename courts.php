<?php
// File: courts.php
// Chức năng: Hiển thị danh sách tất cả các sân, hỗ trợ tìm kiếm và bộ lọc theo giá/trạng thái

require_once 'config/database.php';
require_once 'includes/header.php';

// Nhận các tham số tìm kiếm và lọc từ form GET
$keyword = trim($_GET['keyword'] ?? '');
$filter_price = $_GET['filter_price'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

// Xây dựng câu truy vấn SQL linh hoạt với Prepared Statements
$sql = "SELECT * FROM courts WHERE 1=1";
$params = [];

if (!empty($keyword)) {
    $sql .= " AND (name LIKE ? OR address LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

if (!empty($filter_price)) {
    if ($filter_price == 'low') {
        $sql .= " AND price < 120000";
    } elseif ($filter_price == 'medium') {
        $sql .= " AND price BETWEEN 120000 AND 150000";
    } elseif ($filter_price == 'high') {
        $sql .= " AND price > 150000";
    }
}

if (!empty($filter_status)) {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
}

$sql .= " ORDER BY id DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $courts = $stmt->fetchAll();
} catch (PDOException $e) {
    $courts = [];
}
?>

<div class="container my-4">
    <!-- TIÊU ĐỀ TRANG -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-custom-primary"><i class="fa-solid fa-list me-2"></i>Danh Sách Sân Cầu Lông</h2>
            <p class="text-muted">Tìm kiếm và lựa chọn sân tập ưng ý nhất cho bạn</p>
            <div class="mx-auto bg-success mb-4" style="width: 80px; height: 3px;"></div>
        </div>
    </div>

    <!-- THANH TÌM KIẾM VÀ BỘ LỌC -->
    <div class="card card-custom p-4 mb-5 bg-white">
        <form action="courts.php" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="keyword" class="form-label fw-semibold small">Tìm kiếm sân</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Nhập tên sân hoặc địa chỉ..." value="<?= htmlspecialchars($keyword) ?>">
                </div>
            </div>

            <div class="col-md-3">
                <label for="filter_price" class="form-label fw-semibold small">Lọc theo giá</label>
                <select class="form-select" id="filter_price" name="filter_price">
                    <option value="">-- Tất cả mức giá --</option>
                    <option value="low" <?= $filter_price == 'low' ? 'selected' : '' ?>>Dưới 120.000 VNĐ</option>
                    <option value="medium" <?= $filter_price == 'medium' ? 'selected' : '' ?>>120.000 - 150.000 VNĐ</option>
                    <option value="high" <?= $filter_price == 'high' ? 'selected' : '' ?>>Trên 150.000 VNĐ</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="filter_status" class="form-label fw-semibold small">Trạng thái sân</label>
                <select class="form-select" id="filter_status" name="filter_status">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="active" <?= $filter_status == 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                    <option value="maintenance" <?= $filter_status == 'maintenance' ? 'selected' : '' ?>>Đang bảo trì</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-custom-primary w-100 fw-bold">
                    <i class="fa-solid fa-filter me-1"></i>Lọc
                </button>
                <a href="courts.php" class="btn btn-outline-secondary" title="Đặt lại bộ lọc"><i class="fa-solid fa-rotate-right"></i></a>
            </div>
        </form>
    </div>

    <!-- DANH SÁCH HIỂN THỊ SÂN -->
    <div class="row g-4">
        <?php if (!empty($courts)): ?>
            <?php foreach ($courts as $court): ?>
                <div class="col-md-4">
                    <div class="card card-custom h-100">
                        <div class="position-relative">
                            <img src="assets/images/<?= htmlspecialchars($court['image']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($court['name']) ?>" 
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.src='https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=500&auto=format&fit=crop&q=60'">
                            
                            <!-- Nhãn trạng thái -->
                            <span class="position-absolute top-0 end-0 m-3 badge <?= $court['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                                <?= $court['status'] === 'active' ? 'Hoạt động' : 'Bảo trì' ?>
                            </span>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($court['name']) ?></h5>
                            <p class="card-text text-muted small mb-2"><i class="fa-solid fa-location-dot text-danger me-1"></i><?= htmlspecialchars($court['address']) ?></p>
                            <p class="card-text text-success fw-bold mb-3">
                                <i class="fa-solid fa-tag me-1"></i><?= number_format($court['price'], 0, ',', '.') ?> VNĐ / giờ
                            </p>
                            
                            <div class="mt-auto d-flex gap-2">
                                <a href="court-detail.php?id=<?= $court['id'] ?>" class="btn btn-outline-success btn-sm w-50">
                                    <i class="fa-solid fa-circle-info me-1"></i>Chi tiết
                                </a>
                                <?php if ($court['status'] === 'active'): ?>
                                    <a href="booking.php?court_id=<?= $court['id'] ?>" class="btn btn-custom-primary btn-sm w-50">
                                        <i class="fa-solid fa-bolt me-1"></i>Đặt sân
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm w-50" disabled>Bảo trì</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="card card-custom p-5">
                    <i class="fa-solid fa-face-sad-tear fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Không tìm thấy sân cầu lông nào phù hợp với yêu cầu của bạn!</h5>
                    <p class="text-muted small">Vui lòng thử lại với từ khóa hoặc bộ lọc khác.</p>
                    <div><a href="courts.php" class="btn btn-custom-primary btn-sm mt-2">Xem tất cả sân</a></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>