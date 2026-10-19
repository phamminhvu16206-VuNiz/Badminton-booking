<?php
// File: app/models/user.php
namespace App\Models;

class User extends BaseModel
{
    // 1. Lấy thông tin user theo Email (dùng cho đăng nhập, quên mật khẩu)
    public function getUserByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // 2. Lấy thông tin user theo ID (dùng cho trang cá nhân, phân quyền)
    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function emailExists($email)
    {
        return (bool) $this->statement('SELECT id FROM users WHERE email = ?', [$email])->fetchColumn();
    }

    // 3. Đăng ký / Tạo tài khoản mới
    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, phone, role, avatar) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'] ?? $data['full_name'] ?? '',
            $data['email'],
            $data['password'],
            $data['phone'] ?? null,
            $data['role'] ?? 'user',
            $data['avatar'] ?? 'default-avatar.png'
        ]);
    }

    // 4. Cập nhật thông tin cá nhân & ảnh đại diện
    public function updateProfile($userId, $data)
    {
        $columns = $this->db->query("SHOW COLUMNS FROM users")->fetchAll(\PDO::FETCH_COLUMN);
        $nameColumn = in_array('full_name', $columns, true) ? 'full_name' : 'name';
        $stmt = $this->db->prepare("UPDATE users SET {$nameColumn} = ?, phone = ?, avatar = ? WHERE id = ?");
        return $stmt->execute([
            $data['name'] ?? $data['full_name'] ?? '',
            $data['phone'] ?? null,
            $data['avatar'] ?? 'default-avatar.png',
            $userId
        ]);
    }

    // 5. Lưu OTP và thời gian hết hạn (sau 15 phút)
    public function saveOtp($userId, $otp)
    {
        // Thiết lập múi giờ Việt Nam
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $stmt = $this->db->prepare("UPDATE users SET reset_otp = ?, reset_otp_expiry = ? WHERE id = ?");
        return $stmt->execute([$otp, $expiry, $userId]);
    }

    // 6. Kiểm tra OTP bằng thời gian chuẩn của PHP (Tránh lỗi timezone MySQL)
    public function verifyOtp($email, $otp)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $now = date('Y-m-d H:i:s');

        // Lấy thông tin user có email và OTP tương ứng
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND reset_otp = ?");
        $stmt->execute([$email, $otp]);
        $user = $stmt->fetch();

        // Kiểm tra xem OTP có tồn tại và thời gian hiện tại còn nhỏ hơn thời gian hết hạn không
        if ($user && !empty($user['reset_otp_expiry']) && $user['reset_otp_expiry'] >= $now) {
            return $user;
        }

        return false;
    }

    // 7. Cập nhật mật khẩu mới & Xóa OTP
    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ?, reset_otp = NULL, reset_otp_expiry = NULL WHERE id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    }

    public function getAllUsers()
    {
        return $this->db->query('SELECT * FROM users ORDER BY id ASC')->fetchAll();
    }

    public function deleteUser($id)
    {
        return $this->statement('DELETE FROM users WHERE id = ?', [$id])->rowCount() > 0;
    }

    public function createAdminUser(array $data)
    {
        return (bool) $this->statement(
            'INSERT INTO users (name, email, phone, role, password) VALUES (?, ?, ?, ?, ?)',
            [$data['name'], $data['email'], $data['phone'], $data['role'], $data['password']]
        );
    }

    public function updateAdminUser(array $data)
    {
        if (!empty($data['password'])) {
            return $this->statement(
                'UPDATE users SET name = ?, email = ?, phone = ?, role = ?, password = ? WHERE id = ?',
                [$data['name'], $data['email'], $data['phone'], $data['role'], $data['password'], $data['id']]
            )->execute();
        }
        return $this->statement(
            'UPDATE users SET name = ?, email = ?, phone = ?, role = ? WHERE id = ?',
            [$data['name'], $data['email'], $data['phone'], $data['role'], $data['id']]
        )->execute();
    }
}
