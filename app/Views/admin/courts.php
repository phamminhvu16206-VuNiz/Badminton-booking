<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= htmlspecialchars((defined('APP_BASE_URL') ? APP_BASE_URL : '') . '/admin/', ENT_QUOTES, 'UTF-8') ?>">
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
                    <a href="bookings"><i class="fa-solid fa-calendar-days me-2"></i>Quản lý đặt sân</a>
                    <a href="cancellation-requests"><i class="fa-solid fa-rotate-left me-2"></i>Yêu cầu hủy / hoàn tiền</a>
                    <a href="courts" class="active"><i class="fa-solid fa-layer-group me-2"></i>Quản lý sân & giá</a>
                    <a href="services"><i class="fa-solid fa-concierge-bell me-2"></i>Quản lý dịch vụ</a>
                    <a href="users"><i class="fa-solid fa-users me-2"></i>Quản lý người dùng</a>
                    <a href="../home/index" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-globe me-2"></i>Xem trang web</a>
                    <a href="../auth/logout" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a>
                </nav>
            </div>

            <!-- MAIN CONTENT -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold text-dark">Quản Lý Sân & Giá Thuê</h3>
                        <p class="text-muted small mb-0">Quản lý giá thuê, trạng thái hoạt động, thêm và xóa sân</p>
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
                                    <th>Trạng Thái</th>
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
                                        <td>
                                            <span class="badge <?= ($c['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                <?= ($c['status'] ?? 'active') === 'active' ? 'Đang hoạt động' : 'Đang bảo trì' ?>
                                            </span>
                                        </td>
                                        <td class="text-danger fw-bold fs-5"><?= number_format($c['price_per_hour'] ?? $c['price'] ?? 0) ?> VNĐ</td>
                                        <td class="text-muted small"><?= htmlspecialchars($c['description'] ?? 'Chưa có mô tả') ?></td>
                                        <td class="text-center">
                                            <!-- Nút Sửa -->
                                            <button class="btn btn-sm btn-warning fw-bold me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $c['id'] ?>">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Sửa
                                            </button>
                                            <!-- Nút Xóa -->
                                            <a href="courts?action=delete&id=<?= (int) $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Có chắc chắn muốn xóa sân này không?');">
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
                                                        <h5 class="modal-title fw-bold">Cập nhật sân #<?= $c['id'] ?></h5>
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
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold" for="courtStatus<?= (int) $c['id'] ?>">Trạng Thái:</label>
                                                            <select name="status" id="courtStatus<?= (int) $c['id'] ?>" class="form-select" required>
                                                                <option value="active" <?= ($c['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                                                                <option value="maintenance" <?= ($c['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Đang bảo trì</option>
                                                            </select>
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
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="newCourtStatus">Trạng Thái:</label>
                            <select name="status" id="newCourtStatus" class="form-select" required>
                                <option value="active" selected>Đang hoạt động</option>
                                <option value="maintenance">Đang bảo trì</option>
                            </select>
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