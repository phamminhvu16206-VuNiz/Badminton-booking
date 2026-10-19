<?php
// File: app/controllers/authcontroller.php
namespace App\Controllers;

use App\Models\User;
use App\Core\Mailer;

class AuthController extends BaseController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login($id = null)
    {
        $error = $_SESSION['error'] ?? '';
        $success = $_SESSION['register_success'] ?? $_SESSION['success'] ?? '';
        unset($_SESSION['error'], $_SESSION['register_success'], $_SESSION['success']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->authenticate(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
            if (isset($result['user'])) {
                $user = $result['user'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'] ?? $user['full_name'] ?? '';
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_avatar'] = $user['avatar'] ?? '';
                header('Location: ' . APP_BASE_URL . ($user['role'] === 'admin' ? '/admin/bookings' : '/home/index'));
                exit;
            }
            $error = $result['error'];
        }
        $this->render('client/login', compact('error', 'success'));
    }

    public function register($id = null)
    {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->registerAccount($_POST);
            if (!isset($result['error'])) {
                $_SESSION['register_success'] = 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.';
                header('Location: ' . APP_BASE_URL . '/auth/login');
                exit;
            }
            $error = $result['error'];
        }
        $this->render('client/register', compact('error'));
    }

    public function authenticate($email, $password)
    {
        if ($email === '' || $password === '') {
            return ['error' => 'Vui lòng nhập đầy đủ email và mật khẩu!'];
        }
        $user = $this->userModel->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return ['user' => $user];
        }
        return ['error' => 'Email hoặc mật khẩu không chính xác!'];
    }

    public function registerAccount(array $data)
    {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        if ($name === '' || $email === '' || $phone === '' || $password === '' || $confirmPassword === '') {
            return ['error' => 'Vui lòng điền đầy đủ tất cả các trường!'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Địa chỉ email không hợp lệ!'];
        }
        if (strlen($password) < 6) {
            return ['error' => 'Mật khẩu phải có ít nhất 6 ký tự!'];
        }
        if ($password !== $confirmPassword) {
            return ['error' => 'Mật khẩu nhập lại không khớp!'];
        }
        if ($this->userModel->emailExists($email)) {
            return ['error' => 'Email này đã được đăng ký bởi tài khoản khác!'];
        }

        $created = $this->userModel->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'customer',
        ]);
        return $created ? ['success' => true] : ['error' => 'Đăng ký thất bại, vui lòng thử lại.'];
    }

    public function forgotPassword($id = null)
    {
        if (isset($_GET['action']) && $_GET['action'] === 'new') {
            unset($_SESSION['reset_email']);
            header('Location: ' . APP_BASE_URL . '/auth/forgot-password');
            exit;
        }

        $message = null;
        if (isset($_POST['btn_send_otp'])) {
            $result = $this->handleForgotPassword(trim($_POST['email'] ?? ''));
            $message = isset($result['success'])
                ? ['type' => 'success', 'text' => $result['message']]
                : ['type' => 'danger', 'text' => $result['error']];
        } elseif (isset($_POST['btn_reset_pass'])) {
            $result = $this->handleResetPassword(
                trim($_POST['otp'] ?? ''),
                $_POST['new_password'] ?? '',
                $_POST['confirm_password'] ?? ''
            );
            $message = isset($result['success'])
                ? ['type' => 'success', 'text' => $result['message']]
                : ['type' => 'danger', 'text' => $result['error']];
        }
        $step = isset($_SESSION['reset_email']) ? 2 : 1;
        $this->render('client/forgot-password', compact('message', 'step'));
    }

    public function logout($id = null)
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        session_start();
        $_SESSION['success'] = 'Bạn đã đăng xuất thành công!';
        header('Location: ' . APP_BASE_URL . '/auth/login');
        exit;
    }

    // Xử lý yêu cầu gửi OTP
    public function handleForgotPassword($email)
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Vui lòng nhập địa chỉ email hợp lệ!'];
        }

        $user = $this->userModel->getUserByEmail($email);
        if (!$user) {
            return ['error' => 'Email này chưa được đăng ký trong hệ thống!'];
        }

        // Tạo OTP 6 chữ số ngẫu nhiên
        $otp = random_int(100000, 999999);

        // Lưu OTP vào CSDL
        $this->userModel->saveOtp($user['id'], $otp);

        // Gửi Mail OTP
        $userName = $user['full_name'] ?? $user['name'] ?? $user['fullname'] ?? $user['username'] ?? 'Khách hàng';
        $sent = Mailer::sendOTP($email, $userName, $otp);

        if ($sent) {
            // LƯU EMAIL VÀO SESSION ĐỂ CHUYỂN SANG BƯỚC 2
            $_SESSION['reset_email'] = $email;
            return ['success' => true, 'message' => 'Mã OTP đã được gửi đến email của bạn!'];
        }

        return ['error' => 'Không thể gửi email OTP, vui lòng kiểm tra lại cấu hình Mailer hoặc thử lại sau!'];
    }

    // Xử lý đặt lại mật khẩu
    public function handleResetPassword($otp, $newPassword, $confirmPassword)
    {
        $email = $_SESSION['reset_email'] ?? '';

        if (empty($email)) {
            return ['error' => 'Phiên làm việc đã hết hạn, vui lòng nhập lại email!'];
        }

        if (!preg_match('/^\d{6}$/', (string) $otp)) {
            return ['error' => 'Mã OTP phải gồm đúng 6 chữ số!'];
        }

        if (strlen($newPassword) < 6) {
            return ['error' => 'Mật khẩu mới phải chứa ít nhất 6 ký tự!'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['error' => 'Mật khẩu xác nhận không khớp!'];
        }

        // Kiểm tra OTP trong CSDL
        $user = $this->userModel->verifyOtp($email, $otp);
        if (!$user) {
            return ['error' => 'Mã OTP không chính xác hoặc đã hết hạn!'];
        }

        // Đổi mật khẩu thành công
        $this->userModel->updatePassword($user['id'], $newPassword);
        unset($_SESSION['reset_email']);

        return ['success' => true, 'message' => 'Đổi mật khẩu thành công! Bạn có thể đăng nhập ngay.'];
    }
}
