<?php require_once APP_ROOT . '/app/Views/layouts/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card card-custom p-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-custom-primary"><i class="fa-solid fa-right-to-bracket me-2"></i>Đăng Nhập</h3>
                    <p class="text-muted small">Chào mừng bạn quay trở lại với Badminton Booking</p>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i><?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="auth/login" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Địa chỉ Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <!-- THÊM LINK QUÊN MẬT KHẨU TẠI ĐÂY -->
                        <div class="text-end mt-2">
                            <a href="auth/forgot-password?action=reset_email" class="text-decoration-none text-success small fw-semibold">
                                Quên mật khẩu?
                            </a>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-custom-primary w-100 py-2 fw-bold">
                        <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Đăng Nhập
                    </button>
                </form>

                <div class="text-center mt-3">
                    <p class="text-muted small">Chưa có tài khoản? <a href="auth/register" class="text-success fw-bold text-decoration-none">Đăng ký ngay</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once APP_ROOT . '/app/Views/layouts/footer.php';
?>