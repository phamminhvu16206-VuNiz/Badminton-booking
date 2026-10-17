<?php
// File: admin/courts.php
session_start();
require_once '../config/database.php';

$user_role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id']) || $user_role !== 'admin') {
    $_SESSION['error'] = 'Bạn không có quyền truy cập!';
    header('Location: ../login.php');
    exit();
}

$success = '';
$error = '';

// Xử lý XÓA SÂN
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $court_id = intval($_GET['id'] ?? 0);
    try {
        $stmt_del = $conn->prepare("DELETE FROM courts WHERE id = ?");
        if ($stmt_del->execute([$court_id])) {
            $success = "Xóa sân thành công!";
        } else {
            $error = "Thất bại! Không thể xóa sân.";
        }
    } catch (PDOException $e) {
        $error = "Không thể xóa sân này vì đã có lịch đặt liên quan!";
    }
}

// Xử lý CẬP NHẬT thông tin & giá sân
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_update_court'])) {
    $court_id = intval($_POST['court_id']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);

    if ($price <= 0 || empty($name)) {
        $error = 'Tên sân và giá thuê phải hợp lệ!';
    } else {
        // Kiểm tra tên cột giá trong DB
        $stmt_check = $conn->query("SHOW COLUMNS FROM courts LIKE 'price_per_hour'");
        $price_col = ($stmt_check->rowCount() > 0) ? 'price_per_hour' : 'price';

        $stmt_update = $conn->prepare("UPDATE courts SET name = ?, $price_col = ?, description = ? WHERE id = ?");
        if ($stmt_update->execute([$name, $price, $description, $court_id])) {
            $success = "Cập nhật thông tin sân thành công!";
        } else {
            $error = "Cập nhật thất bại!";
        }
    }
}

// Xử lý THÊM SÂN MỚI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_add_court'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);

    if ($price <= 0 || empty($name)) {
        $error = 'Vui lòng nhập tên sân và giá hợp lệ!';
    } else {
        $stmt_check = $conn->query("SHOW COLUMNS FROM courts LIKE 'price_per_hour'");
        $price_col = ($stmt_check->rowCount() > 0) ? 'price_per_hour' : 'price';

        $stmt_add = $conn->prepare("INSERT INTO courts (name, $price_col, description) VALUES (?, ?, ?)");
        if ($stmt_add->execute([$name, $price, $description])) {
            $success = "Thêm sân mới thành công!";
        } else {
            $error = "Thêm sân thất bại!";
        }
    }
}

$courts = $conn->query("SELECT * FROM courts ORDER BY id ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sân & Giá - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #1e293b;
            color: white;
        }

        .sidebar a {
            color: #94a3b8;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            border-radius: 6px;
            margin-bottom: 5px;
            transition: 0.2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #0f172a;
            color: white;
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
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
                    <a href="index.php"><i class="fa-solid fa-calendar-days me-2"></i>Quản lý đặt sân</a>
                    <a href="courts.php" class="active"><i class="fa-solid fa-layer-group me-2"></i>Quản lý sân & giá</a>
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
                        <h3 class="fw-bold text-dark">Quản Lý Sân & Giá Thuê</h3>
                        <p class="text-muted small mb-0">Cập nhật đơn giá theo giờ, thêm và xóa sân</p>
                    </div>
                    <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#addCourtModal">
                        <i class="fa-solid fa-plus me-1"></i> Thêm Sân Mới
                    </button>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <div class="card card-custom p-4 bg-white">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#ID</th>
                                    <th>Tên Sân</th>
                                    <th>Giá Thuê / Giờ</th>
                                    <th>Mô Tả</th>
                                    <th class="text-center">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courts as $c): ?>
                                    <tr>
                                        <td><strong>#<?= $c['id'] ?></strong></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($c['name']) ?></td>
                                        <td class="text-danger fw-bold fs-5"><?= number_format($c['price_per_hour'] ?? $c['price'] ?? 0) ?> VNĐ</td>
                                        <td class="text-muted small"><?= htmlspecialchars($c['description'] ?? 'Chưa có mô tả') ?></td>
                                        <td class="text-center">
                                            <!-- Nút Sửa -->
                                            <button class="btn btn-sm btn-warning fw-bold me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $c['id'] ?>">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Sửa Giá
                                            </button>
                                            <!-- Nút Xóa -->
                                            <a href="courts.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Có chắc chắn muốn xóa sân này không?');">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Modal Sửa Giá Sân -->
                                    <div class="modal fade" id="editModal<?= $c['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content rounded-4">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Sửa Giá Sân #<?= $c['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="court_id" value="<?= $c['id'] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Tên Sân:</label>
                                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($c['name']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Giá Thuê 1 Giờ (VNĐ):</label>
                                                            <input type="number" name="price" class="form-control" value="<?= $c['price_per_hour'] ?? $c['price'] ?? 0 ?>" step="1000" min="0" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Mô Tả Sân:</label>
                                                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($c['description'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" name="btn_update_court" class="btn btn-success fw-bold">Lưu Thay Đổi</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm Sân Mới -->
    <div class="modal fade" id="addCourtModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Thêm Sân Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên Sân:</label>
                            <input type="text" name="name" class="form-control" placeholder="VD: Sân Cầu Lông Số 8" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Giá Thuê 1 Giờ (VNĐ):</label>
                            <input type="number" name="price" class="form-control" placeholder="150000" step="1000" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô Tả:</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Mô tả tiện ích sân..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="btn_add_court" class="btn btn-success fw-bold">Thêm Sân</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>