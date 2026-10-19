<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tự động nhận diện trang hiện tại để active menu
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$currentRoute = trim((string) ($_GET['url'] ?? $requestPath), '/');
$basePath = defined('APP_BASE_URL') ? trim(APP_BASE_URL, '/') : '';
if ($basePath !== '' && strpos($currentRoute, $basePath . '/') === 0) {
    $currentRoute = substr($currentRoute, strlen($basePath) + 1);
}
$currentRoute = strtolower($currentRoute);
$currentRoute = [
    '' => 'home/index',
    'index.php' => 'home/index',
    'courts.php' => 'home/courts',
    'my-bookings.php' => 'booking/mine',
    'login.php' => 'auth/login',
    'register.php' => 'auth/register',
    'profile.php' => 'user/profile',
][$currentRoute] ?? $currentRoute;

if (!empty($_SESSION['user_id'])) {
    if (empty($_SESSION['user_avatar'])) {
        require_once APP_ROOT . '/autoload.php';
        $userModel = new \App\Models\User();
        $uInfo = $userModel->getUserById($_SESSION['user_id']);
        $_SESSION['user_avatar'] = $uInfo['avatar'] ?? '';
    }
}

$currentAvatar = $_SESSION['user_avatar'] ?? '';

// Tự động nhận diện đường dẫn tùy thuộc file đang chạy nằm ở thư mục gốc hay trong thư mục con (admin)
$is_admin_folder = strpos($requestPath, '/admin/') !== false || trim((string) ($_GET['url'] ?? ''), '/') === 'admin';
$baseHref = (defined('APP_BASE_URL') ? APP_BASE_URL : '') . ($is_admin_folder ? '/admin/' : '/');

$newAvatar = APP_ROOT . '/public/uploads/avatars/' . basename($currentAvatar);
$oldAvatar = APP_ROOT . '/assets/images/avatars/' . basename($currentAvatar);
$avatarUrlPrefix = (defined('APP_BASE_URL') ? APP_BASE_URL : '') . '/';
$headerAvatarPath = !empty($currentAvatar) && is_file($newAvatar)
    ? $avatarUrlPrefix . 'public/uploads/avatars/' . rawurlencode(basename($currentAvatar))
    : (!empty($currentAvatar) && is_file($oldAvatar)
        ? $avatarUrlPrefix . 'assets/images/avatars/' . rawurlencode(basename($currentAvatar))
        : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8') ?>">
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
            <a class="navbar-brand fw-bold" href="home/index">
                <i class="fa-solid fa-table-tennis-paddle-ball me-2"></i>BADMINTON BOOKING
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link <?= ($currentRoute === 'home/index') ? 'active fw-bold' : '' ?>" href="home/index">
                            <i class="fa-solid fa-house me-1"></i> Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentRoute === 'home/courts') ? 'active fw-bold' : '' ?>" href="home/courts">
                            <i class="fa-solid fa-list me-1"></i> Danh sách sân
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentRoute === 'booking/mine') ? 'active fw-bold' : '' ?>" href="booking/mine">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Lịch đặt của tôi
                        </a>
                    </li>

                </ul>

                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= $is_admin_folder ? '../user/profile' : 'user/profile' ?>" class="text-white text-decoration-none d-flex align-items-center fw-semibold">
                            <img src="<?= $headerAvatarPath ?>" class="rounded-circle me-2 border border-white shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                            <span><?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['full_name'] ?? 'Tài khoản') ?></span>
                        </a>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <a href="admin/bookings" class="btn btn-warning btn-sm"><i class="fa-solid fa-user-gear me-1"></i> Admin</a>
                        <?php endif; ?>
                        <a href="auth/logout" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất</a>
                    <?php else: ?>
                        <a href="auth/login" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-right-to-bracket me-1"></i> Đăng nhập</a>
                        <a href="auth/register" class="btn btn-light text-success btn-sm fw-bold"><i class="fa-solid fa-user-plus me-1"></i> Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-4">