<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= htmlspecialchars((defined('APP_BASE_URL') ? APP_BASE_URL : '') . '/admin/', ENT_QUOTES, 'UTF-8') ?>">
    <title>Quản Lý Người Dùng - Admin</title>
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
                    <a href="services"><i class="fa-solid fa-concierge-bell me-2"></i>Quản lý dịch vụ</a>
                    <a href="users" class="active"><i class="fa-solid fa-users me-2"></i>Quản lý người dùng</a>
                    <a href="../home/index" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-globe me-2"></i>Xem trang web</a>
                    <a href="../auth/logout" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a>
                </nav>
            </div>

            <!-- MAIN CONTENT -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold text-dark">Quản Lý Người Dùng & Phân Quyền</h3>
                        <p class="text-muted small mb-0">Thêm, sửa, xóa tài khoản và cấp quyền quản trị viên hệ thống</p>
                    </div>
                    <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fa-solid fa-user-plus me-1"></i> Thêm Người Dùng
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
                                    <th>Avatar</th>
                                    <th>Họ và Tên</th>
                                    <th>Email</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Vai Trò</th>
                                    <th class="text-center">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <?php
                                    $avatarFile = $u['avatar'] ?? '';
                                    $avatarName = basename($avatarFile);
                                    $newAvatarPath = APP_ROOT . '/public/uploads/avatars/' . $avatarName;
                                    $oldAvatarPath = APP_ROOT . '/assets/images/avatars/' . $avatarName;
                                    $uAvatarPath = !empty($avatarName) && file_exists($newAvatarPath)
                                        ? '../public/uploads/avatars/' . rawurlencode($avatarName)
                                        : (!empty($avatarName) && file_exists($oldAvatarPath)
                                            ? '../assets/images/avatars/' . rawurlencode($avatarName)
                                            : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png');
                                    ?>
                                    <tr>
                                        <td><strong>#<?= $u['id'] ?></strong></td>
                                        <td>
                                            <img src="<?= $uAvatarPath ?>" class="rounded-circle shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                                        </td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($u['name'] ?? $u['full_name'] ?? 'Chưa đặt tên') ?></td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td><?= htmlspecialchars($u['phone'] ?? 'Chưa cập nhật') ?></td>
                                        <td>
                                            <?php if (($u['role'] ?? '') === 'admin'): ?>
                                                <span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-user-shield me-1"></i> Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary px-3 py-2">Khách hàng</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-warning fw-bold me-1" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $u['id'] ?>">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Sửa
                                            </button>
                                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                                <a href="users?action=delete&id=<?= (int) $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Có chắc chắn muốn xóa tài khoản này không?');">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Modal Sửa User -->
                                    <div class="modal fade" id="editUserModal<?= $u['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content rounded-4">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Sửa Người Dùng #<?= $u['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Họ và Tên:</label>
                                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($u['name'] ?? $u['full_name'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Email:</label>
                                                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($u['email']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Số điện thoại:</label>
                                                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($u['phone'] ?? '') ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold text-danger">Phân Quyền:</label>
                                                            <select name="role" class="form-select">
                                                                <option value="user" <?= (($u['role'] ?? '') === 'user') ? 'selected' : '' ?>>Khách hàng (User)</option>
                                                                <option value="admin" <?= (($u['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Quản trị viên (Admin)</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Mật khẩu mới (Bỏ trống nếu không đổi):</label>
                                                            <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu mới...">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" name="btn_update_user" class="btn btn-success fw-bold">Lưu Thay Đổi</button>
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

    <!-- Modal Thêm User Mới -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Thêm Người Dùng Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ và Tên:</label>
                            <input type="text" name="name" class="form-control" placeholder="VD: Nguyễn Văn A" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email:</label>
                            <input type="email" name="email" class="form-control" placeholder="user@gmail.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Số điện thoại:</label>
                            <input type="text" name="phone" class="form-control" placeholder="0912345678">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Phân Quyền:</label>
                            <select name="role" class="form-select">
                                <option value="user">Khách hàng (User)</option>
                                <option value="admin">Quản trị viên (Admin)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật khẩu mặc định:</label>
                            <input type="password" name="password" class="form-control" placeholder="Mặc định là 123456 nếu bỏ trống">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="btn_add_user" class="btn btn-success fw-bold">Thêm Người Dùng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>