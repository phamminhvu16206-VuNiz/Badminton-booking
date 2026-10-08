<?php
// File: register.php (Cập nhật phần xử lý thành công và chuyển hướng)

require_once 'config/database.php';
require_once 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng điền đầy đủ tất cả các trường!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Địa chỉ email không hợp lệ!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu nhập lại không khớp!';
    } else {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Email này đã được đăng ký bởi tài khoản khác!';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $insert = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')");
                if ($insert->execute([$name, $email, $phone, $hashed_password])) {
                    // Lưu thông báo vào session và chuyển hướng sang trang đăng nhập
                    $_SESSION['register_success'] = 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.';
                    header('Location: login.php');
                    exit();
                } else {
                    $error = 'Đăng ký thất bại, vui lòng thử lại.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-custom p-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-custom-primary"><i class="fa-solid fa-user-plus me-2"></i>Đăng Ký Tài Khoản</h3>
                    <p class="text-muted small">Tạo tài khoản để đặt sân và quản lý lịch dễ dàng</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Họ và tên</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Địa chỉ Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-semibold">Số điện thoại</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-semibold">Nhập lại mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-custom-primary w-100 py-2 fw-bold">
                        <i class="fa-solid fa-user-check me-2"></i>Đăng Ký
                    </button>
                </form>

                <div class="text-center mt-3">
                    <p class="text-muted small">Đã có tài khoản? <a href="login.php" class="text-success fw-bold text-decoration-none">Đăng nhập ngay</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>