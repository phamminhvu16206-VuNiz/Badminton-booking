<?php
// File: app/core/mailer.php
namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Nạp thủ công 3 file bắt buộc của PHPMailer từ thư mục vendor
require_once __DIR__ . '/../../vendor/PHPMailer/Exception.php';
require_once __DIR__ . '/../../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../vendor/PHPMailer/SMTP.php';

class Mailer
{

    /**
     * Cấu hình kết nối SMTP dùng chung cho hệ thống
     */
    private static function getMailer()
    {
        $mail = new PHPMailer(true);

        // --- Cấu hình Server SMTP ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        $smtpUser         = 'phamminhvu16206@gmail.com'; // Thay email của bạn vào đây
        $smtpPassword     = 'kohn lmuf igck tlqs';   // Thay Mật khẩu ứng dụng 16 ký tự

        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // SetFrom TRÙNG KHỚP với $smtpUser để Gmail không chặn mail hoặc đẩy vào Spam
        $mail->setFrom($smtpUser, 'Badminton Booking');

        // Bổ sung Header ưu tiên cao cho Email giao dịch
        $mail->Priority = 1;
        $mail->addCustomHeader('X-MSMail-Priority', 'High');
        $mail->addCustomHeader('Importance', 'High');

        return $mail;
    }

    /**
     * 1. Gửi Email Hóa Đơn Xác Nhận Đặt Sân
     */
    public static function sendInvoice($toEmail, $toName, $bookingId, $courtName, $date, $timeSlot, $totalAmount)
    {
        try {
            $mail = self::getMailer();
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);

            $mail->Subject = "Hóa Đơn Xác Nhận Đặt Sân - Mã Đơn #{$bookingId}";
            $formattedPrice = number_format($totalAmount) . ' VNĐ';
            $bookingCode = BookingQr::bookingCode($bookingId);
            $qrCodeUrl = htmlspecialchars(BookingQr::imageUrl($bookingId, 240), ENT_QUOTES, 'UTF-8');

            // Nội dung HTML hóa đơn
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background-color: #ffffff;'>
                    <div style='background-color: #198754; color: #ffffff; padding: 20px; text-align: center;'>
                        <h2 style='margin: 0; font-size: 20px;'>XÁC NHẬN ĐẶT SÂN THÀNH CÔNG</h2>
                    </div>
                    <div style='padding: 25px;'>
                        <p style='margin-top: 0;'>Xin chào <strong>" . htmlspecialchars($toName) . "</strong>,</p>
                        <p>Cảm ơn bạn đã thanh toán và đặt sân tại <strong>Badminton Booking</strong>. Dưới đây là thông tin chi tiết đơn đặt sân của bạn:</p>
                        
                        <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                            <tr style='background-color: #f8f9fa;'>
                                <td style='padding: 12px; border: 1px solid #dee2e6; font-weight: bold;'>Mã Đơn Hàng:</td>
                                <td style='padding: 12px; border: 1px solid #dee2e6; color: #198754; font-weight: bold;'>#{$bookingId}</td>
                            </tr>
                            <tr>
                                <td style='padding: 12px; border: 1px solid #dee2e6; font-weight: bold;'>Sân Đặt:</td>
                                <td style='padding: 12px; border: 1px solid #dee2e6;'>" . htmlspecialchars($courtName) . "</td>
                            </tr>
                            <tr style='background-color: #f8f9fa;'>
                                <td style='padding: 12px; border: 1px solid #dee2e6; font-weight: bold;'>Ngày Sử Dụng:</td>
                                <td style='padding: 12px; border: 1px solid #dee2e6;'>{$date}</td>
                            </tr>
                            <tr>
                                <td style='padding: 12px; border: 1px solid #dee2e6; font-weight: bold;'>Khung Giờ:</td>
                                <td style='padding: 12px; border: 1px solid #dee2e6;'>{$timeSlot}</td>
                            </tr>
                            <tr style='background-color: #f8f9fa;'>
                                <td style='padding: 12px; border: 1px solid #dee2e6; font-weight: bold;'>Tổng Số Tiền:</td>
                                <td style='padding: 12px; border: 1px solid #dee2e6; color: #dc3545; font-weight: bold; font-size: 16px;'>{$formattedPrice}</td>
                            </tr>
                        </table>

                        <p style='font-size: 13px; color: #6c757d;'>Nội dung giao dịch đã ghi nhận: <strong>DATSAN {$bookingId}</strong>.</p>
                        <div style='margin: 24px auto; padding: 18px; max-width: 280px; text-align: center; border: 1px solid #dee2e6; border-radius: 8px;'>
                            <p style='margin: 0 0 10px; font-weight: bold;'>Mã QR đơn đặt sân</p>
                            <img src='{$qrCodeUrl}' width='200' height='200' alt='Mã QR đơn đặt sân #{$bookingId}' style='display: block; width: 200px; height: 200px; margin: 0 auto;'>
                            <p style='margin: 10px 0 0; color: #6c757d; font-size: 12px;'>Mã đơn: <strong>{$bookingCode}</strong></p>
                        </div>
                        <hr style='border: none; border-top: 1px solid #eeeeee; margin: 20px 0;'>
                        <p style='font-size: 11px; color: #999999; text-align: center; margin-bottom: 0;'>
                            Đây là email xác nhận tự động từ hệ thống Badminton Booking. Vui lòng không phản hồi lại email này.
                        </p>
                    </div>
                </div>
            ";

            // Bản thuần cho bộ lọc Spam
            $mail->AltBody = "Xin chào {$toName}, Đơn đặt sân #{$bookingId} ({$courtName}, Ngày: {$date}, Khung giờ: {$timeSlot}) đã thanh toán thành công. Tổng tiền: {$formattedPrice}. Mã QR chứa mã đơn {$bookingCode}.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Lỗi gửi Mail Invoice: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 2. Gửi Email Mã OTP Khôi Phục Mật Khẩu
     */
    public static function sendOTP($toEmail, $toName, $otp)
    {
        try {
            $mail = self::getMailer();
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);

            $mail->Subject = "Mã xác minh khôi phục mật khẩu - Badminton Booking";

            // Nội dung HTML thông báo mã OTP
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 520px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; padding: 25px; background-color: #ffffff;'>
                    <h3 style='color: #198754; text-align: center; margin-top: 0; margin-bottom: 20px; font-size: 22px;'>BADMINTON BOOKING</h3>
                    <p style='margin-top: 0;'>Xin chào <strong>" . htmlspecialchars($toName) . "</strong>,</p>
                    <p>Chúng tôi nhận được yêu cầu lấy lại mật khẩu cho tài khoản liên kết với địa chỉ email này.</p>
                    <p>Mã xác minh OTP của bạn là:</p>
                    
                    <div style='text-align: center; margin: 25px 0;'>
                        <span style='font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #198754; background-color: #f8f9fa; padding: 12px 28px; border-radius: 6px; border: 1px solid #198754; display: inline-block;'>{$otp}</span>
                    </div>

                    <p style='color: #6c757d; font-size: 13px; line-height: 1.6; margin-bottom: 20px;'>
                        * Mã xác minh có hiệu lực trong vòng <strong>15 phút</strong>.<br>
                        * Tuyệt đối không chia sẻ mã này cho bất kỳ ai khác để đảm bảo an toàn tài khoản.<br>
                        * Nếu bạn không yêu cầu khôi phục mật khẩu, vui lòng bỏ qua email này.
                    </p>
                    
                    <hr style='border: none; border-top: 1px solid #eeeeee; margin: 20px 0;'>
                    <p style='font-size: 11px; color: #999999; text-align: center; margin-bottom: 0;'>
                        Trân trọng,<br>Đội ngũ hỗ trợ Badminton Booking System
                    </p>
                </div>
            ";

            // Bản thuần giúp tăng uy tín với bộ lọc Mail
            $mail->AltBody = "Xin chào {$toName}, Mã OTP khôi phục mật khẩu Badminton Booking của bạn là: {$otp}. Mã có hiệu lực trong 15 phút.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Lỗi gửi Mail OTP: " . $e->getMessage());
            return false;
        }
    }
}
