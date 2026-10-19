<?php
$baseUrl = defined('APP_BASE_URL') ? APP_BASE_URL : '';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quên Mật Khẩu - Badminton Booking</title>
    <base href="<?= htmlspecialchars($baseUrl . '/', ENT_QUOTES, 'UTF-8') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light d-flex align-items-center min-vh-100 py-4">

    <div class="container" style="max-width: 430px;">
        <div class="card border-0 shadow-lg rounded-4 p-3 bg-white">
            <div class="card-body">

                <div class="text-center mb-3">
                    <h3 class="fw-bold text-success mb-1">
                        <i class="fa-solid fa-key me-2"></i>Quên Mật Khẩu
                    </h3>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show small" role="alert">
                        <?= $message['text'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                    <!-- BƯỚC 1: LUÔN HIỂN THỊ Ô NHẬP EMAIL NẾU CHƯA GỬI OTP -->
                    <form method="POST" action="auth/forgot-password">
                        <p class="text-muted small mb-3 text-center">Nhập email đăng ký của bạn để nhận mã xác minh OTP.</p>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Địa chỉ Email</label>
                            <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
                        </div>
                        <button type="submit" name="btn_send_otp" class="btn btn-success w-100 fw-bold py-2 rounded-3">
                            Gửi Mã OTP
                        </button>
                    </form>
                <?php else: ?>
                    <!-- BƯỚC 2: NHẬP OTP & MẬT KHẨU MỚI (Hiện sau khi gửi mail OTP thành công) -->
                    <form method="POST" action="auth/forgot-password">
                        <p class="text-muted small mb-1 text-center">
                            Mã OTP đã gửi đến: <strong class="text-dark"><?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?></strong>
                        </p>
                        <div class="text-center mb-3">
                            <a href="auth/forgot-password?action=new" class="text-decoration-none text-danger small fw-semibold">
                                <i class="fa-solid fa-arrow-left me-1"></i>Nhập email khác
                            </a>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary"></label>
                            <input type="text" name="otp" class="form-control text-center fs-5 fw-bold" placeholder="Nhập mã OTP" maxlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Nhập ít nhất 6 ký tự" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới" required>
                        </div>
                        <button type="submit" name="btn_reset_pass" class="btn btn-primary w-100 fw-bold py-2 mb-2 rounded-3">
                            Xác Nhận Đổi Mật Khẩu
                        </button>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="auth/login" class="text-decoration-none text-muted small">
                        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại trang Đăng nhập
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>