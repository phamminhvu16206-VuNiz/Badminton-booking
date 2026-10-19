<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= htmlspecialchars((defined('APP_BASE_URL') ? APP_BASE_URL : '') . '/admin/', ENT_QUOTES, 'UTF-8') ?>">
    <title>Quản Lý Dịch Vụ - Admin</title>
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
                    <a href="courts"><i class="fa-solid fa-layer-group me-2"></i>Quản lý sân & giá</a>
                    <a href="services" class="active"><i class="fa-solid fa-concierge-bell me-2"></i>Quản lý dịch vụ</a>
                    <a href="users"><i class="fa-solid fa-users me-2"></i>Quản lý người dùng</a>
                    <a href="../home/index" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-globe me-2"></i>Xem trang web</a>
                    <a href="../auth/logout" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a>
                </nav>
            </div>

            <!-- MAIN CONTENT -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold text-dark">Quản Lý Dịch Vụ Bổ Sung</h3>
                        <p class="text-muted small mb-0">Thay đổi đơn giá, thêm và xóa dịch vụ bổ sung</p>
                    </div>
                    <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="fa-solid fa-plus me-1"></i> Thêm Dịch Vụ
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
                                    <th>Tên Dịch Vụ</th>
                                    <th>Đơn Giá</th>
                                    <th>Đơn Vị Tính</th>
                                    <th class="text-center">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $s): ?>
                                    <tr>
                                        <td><strong>#<?= $s['id'] ?></strong></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($s['name']) ?></td>
                                        <td class="text-danger fw-bold fs-5"><?= number_format($s['price']) ?> VNĐ</td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($s['unit']) ?></span></td>
                                        <td class="text-center">
                                            <!-- Nút Sửa -->
                                            <button class="btn btn-sm btn-warning fw-bold me-1" data-bs-toggle="modal" data-bs-target="#editService<?= $s['id'] ?>">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Sửa Giá
                                            </button>
                                            <!-- Nút Xóa -->
                                            <a href="services?action=delete&id=<?= (int) $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Có chắc chắn muốn xóa dịch vụ này không?');">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Modal Sửa Dịch Vụ -->
                                    <div class="modal fade" id="editService<?= $s['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content rounded-4">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Sửa Dịch Vụ #<?= $s['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Tên Dịch Vụ:</label>
                                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($s['name']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Đơn Giá (VNĐ):</label>
                                                            <input type="number" name="price" class="form-control" value="<?= $s['price'] ?>" step="1000" min="0" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Đơn Vị Tính:</label>
                                                            <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($s['unit']) ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" name="btn_update_service" class="btn btn-success fw-bold">Lưu Thay Đổi</button>
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

    <!-- Modal Thêm Dịch Vụ Mới -->
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Thêm Dịch Vụ Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên Dịch Vụ:</label>
                            <input type="text" name="name" class="form-control" placeholder="VD: Khăn lạnh" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Đơn Giá (VNĐ):</label>
                            <input type="number" name="price" class="form-control" placeholder="5000" step="1000" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Đơn Vị Tính:</label>
                            <input type="text" name="unit" class="form-control" placeholder="VD: cái, chai, lượt" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="btn_add_service" class="btn btn-success fw-bold">Thêm Dịch Vụ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>