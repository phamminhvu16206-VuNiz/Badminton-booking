<?php
// File: includes/header.php
// Chức năng: Khởi động session, nạp Bootstrap 5, FontAwesome và hiển thị Navbar chung

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
                        <a class="nav-link active" href="index.php"><i class="fa-solid fa-house me-1"></i> Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courts.php"><i class="fa-solid fa-list me-1"></i> Danh sách sân</a>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="my-bookings.php"><i class="fa-solid fa-clock-rotate-left me-1"></i> Lịch đặt của tôi</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="text-white me-2">
                            <i class="fa-solid fa-user-circle me-1"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Tài khoản') ?>
                        </span>
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