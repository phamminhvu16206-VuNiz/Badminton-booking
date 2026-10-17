<?php
$userAvatar = $_SESSION['user_avatar'] ?? '';
$headerAvatarPath = !empty($userAvatar) && file_exists(__DIR__ . '/../assets/images/avatars/' . $userAvatar)
    ? 'assets/images/avatars/' . $userAvatar
    : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tự động nhận diện trang hiện tại để active menu
$currentPage = basename($_SERVER['PHP_SELF']);

if (!empty($_SESSION['user_id'])) {
    if (empty($_SESSION['user_avatar'])) {
        require_once __DIR__ . '/../config/database.php';
        $stmtAvt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmtAvt->execute([$_SESSION['user_id']]);
        $uInfo = $stmtAvt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['user_avatar'] = $uInfo['avatar'] ?? '';
    }
}

$currentAvatar = $_SESSION['user_avatar'] ?? '';

// Tự động nhận diện đường dẫn tùy thuộc file đang chạy nằm ở thư mục gốc hay trong thư mục con (admin)
$is_admin_folder = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$prefix = $is_admin_folder ? '../' : '';

$headerAvatarPath = !empty($currentAvatar) && file_exists(__DIR__ . '/../assets/images/avatars/' . $currentAvatar)
    ? $prefix . 'assets/images/avatars/' . $currentAvatar
    : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BADMINTON BOOKING - Đặt Sân Cầu Lông Online</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-custom-primary sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fa-solid fa-table-tennis-paddle-ball me-2"></i>BADMINTON BOOKING
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage === 'index.php') ? 'active fw-bold' : '' ?>" href="index.php">
                            <i class="fa-solid fa-house me-1"></i> Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage === 'courts.php') ? 'active fw-bold' : '' ?>" href="courts.php">
                            <i class="fa-solid fa-list me-1"></i> Danh sách sân
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage === 'my-bookings.php') ? 'active fw-bold' : '' ?>" href="my-bookings.php">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Lịch đặt của tôi
                        </a>
                    </li>

                </ul>

                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= $is_admin_folder ? '../profile.php' : 'profile.php' ?>" class="text-white text-decoration-none d-flex align-items-center fw-semibold">
                            <img src="<?= $headerAvatarPath ?>" class="rounded-circle me-2 border border-white shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                            <span><?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['full_name'] ?? 'Tài khoản') ?></span>
                        </a>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <a href="admin/index.php" class="btn btn-warning btn-sm"><i class="fa-solid fa-user-gear me-1"></i> Admin</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-right-to-bracket me-1"></i> Đăng nhập</a>
                        <a href="register.php" class="btn btn-light text-success btn-sm fw-bold"><i class="fa-solid fa-user-plus me-1"></i> Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-4">