<?php
// File: app/controllers/authcontroller.php
namespace App\Controllers;

use App\Models\User;
use App\Core\Mailer;

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
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
        $otp = rand(100000, 999999);

        // Lưu OTP vào CSDL
        $this->userModel->saveOtp($user['id'], $otp);

        // Gửi Mail OTP
        $userName = $user['fullname'] ?? $user['username'] ?? 'Khách hàng';
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

        if (empty($otp) || strlen($otp) !== 6) {
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
