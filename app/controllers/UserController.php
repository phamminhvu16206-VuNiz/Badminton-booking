<?php

namespace App\Controllers;

use App\Models\User;

class UserController extends BaseController
{
    public function profile($id = null)
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . APP_BASE_URL . '/auth/login');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        $userModel = new User();
        $user = $userModel->getUserById($userId);
        if (!$user) {
            http_response_code(404);
            echo 'Không tìm thấy tài khoản.';
            return;
        }

        $message = '';
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_update_profile'])) {
            $fullName = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $avatar = $user['avatar'] ?? 'default-avatar.png';
            $uploadedFile = null;

            if ($fullName === '') {
                $error = 'Họ và tên không được để trống.';
            }

            if ($error === '' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['avatar'];
                $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
                $mimeType = '';
                if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    $error = 'Tải ảnh lên không thành công.';
                } elseif (($file['size'] ?? 0) > 2 * 1024 * 1024) {
                    $error = 'File ảnh quá lớn! Vui lòng chọn ảnh dưới 2MB.';
                } elseif (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    $error = 'Định dạng ảnh không hợp lệ! Chỉ chấp nhận JPG, PNG, WEBP.';
                } else {
                    $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $fileInfo->file($file['tmp_name']);
                    $allowedMimeTypes = [
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'webp' => 'image/webp',
                    ];
                    if (($allowedMimeTypes[$extension] ?? '') !== $mimeType) {
                        $error = 'Nội dung file không đúng định dạng ảnh đã chọn.';
                    } else {
                        $uploadDirectory = APP_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
                        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                            throw new \RuntimeException('Không thể tạo thư mục lưu avatar.');
                        }
                        $avatar = 'avatar_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                        $uploadedFile = $uploadDirectory . DIRECTORY_SEPARATOR . $avatar;
                        if (!move_uploaded_file($file['tmp_name'], $uploadedFile)) {
                            $uploadedFile = null;
                            $error = 'Không thể lưu file ảnh vào thư mục tải lên.';
                        }
                    }
                }
            }

            if ($error === '') {
                if ($userModel->updateProfile($userId, ['name' => $fullName, 'phone' => $phone, 'avatar' => $avatar])) {
                    $_SESSION['user_name'] = $fullName;
                    $_SESSION['full_name'] = $fullName;
                    $_SESSION['user_avatar'] = $avatar;
                    $message = 'Cập nhật thông tin cá nhân thành công!';
                    $user = $userModel->getUserById($userId);
                } else {
                    if ($uploadedFile !== null && is_file($uploadedFile)) {
                        unlink($uploadedFile);
                    }
                    $error = 'Lỗi hệ thống! Không thể cập nhật thông tin.';
                }
            }
        }

        $displayName = $user['full_name'] ?? $user['name'] ?? 'Chưa đặt tên';
        $this->render('client/profile', compact('user', 'message', 'error', 'displayName'));
    }
}
