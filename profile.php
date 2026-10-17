<?php
// File: profile.php - Xem và sửa thông tin cá nhân dạng có nút chuyển đổi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/config/database.php';

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Lấy thông tin user hiện tại
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý Cập nhật thông tin & Upload ảnh
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_update_profile'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $avatar = $user['avatar'] ?? 'default-avatar.png';

    // Upload Ảnh đại diện nếu có chọn file mới
    if (!empty($_FILES['avatar']['name'])) {
        $file = $_FILES['avatar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            if ($file['size'] <= 2 * 1024 * 1024) { // Tối đa 2MB
                $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/assets/images/avatars/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
                    $avatar = $newFileName;
                } else {
                    $error = 'Không thể lưu file ảnh vào thư mục!';
                }
            } else {
                $error = 'File ảnh quá lớn! Vui lòng chọn ảnh dưới 2MB.';
            }
        } else {
            $error = 'Định dạng ảnh không hợp lệ! Chỉ chấp nhận JPG, PNG, WEBP.';
        }
    }

    if (empty($error)) {
        // Hỗ trợ cả cột 'full_name' hoặc 'name'
        $columnName = array_key_exists('full_name', $user) ? 'full_name' : 'name';

        $updateStmt = $conn->prepare("UPDATE users SET $columnName = ?, phone = ?, avatar = ? WHERE id = ?");
        if ($updateStmt->execute([$fullName, $phone, $avatar, $userId])) {
            // CẬP NHẬT SESSION NGAY VÀ LUÔN NÈ CON VỢ
            $_SESSION['user_name'] = $fullName;
            $_SESSION['full_name'] = $fullName;
            $_SESSION['user_avatar'] = $avatar; // Dòng này giúp Header tự đổi ảnh

            $message = 'Cập nhật thông tin cá nhân thành công!';
            // Reload lại thông tin
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Lỗi hệ thống! Không thể cập nhật thông tin.';
        }
    }
}

$displayName = $user['full_name'] ?? $user['name'] ?? 'Chưa đặt tên';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5" style="max-width: 800px;">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold"><i class="fa-solid fa-user-gear me-2"></i>Thông Tin Cá Nhân</h4>
            <button type="button" id="btnEnableEdit" class="btn btn-light btn-sm text-success fw-bold px-3 rounded-pill" onclick="toggleEditMode()">
                <i class="fa-solid fa-pen-to-square me-1"></i> Sửa Thông Tin
            </button>
        </div>
        <div class="card-body p-4 p-md-5 bg-white">

            <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="profileForm">
                <div class="row align-items-center g-4 mb-4">
                    <!-- Ảnh đại diện -->
                    <div class="col-md-4 text-center">
                        <div class="position-relative d-inline-block">
                            <?php
                            $avatarFile = $user['avatar'] ?? '';
                            $avatarPath = !empty($avatarFile) && file_exists(__DIR__ . '/assets/images/avatars/' . $avatarFile)
                                ? 'assets/images/avatars/' . $avatarFile
                                : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                            ?>
                            <img src="<?= $avatarPath ?>" id="avatarPreview" class="rounded-circle img-thumbnail shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">

                            <!-- Nút chọn ảnh ẩn mặc định, chỉ hiện khi bật chế độ sửa -->
                            <label for="avatarInput" id="cameraBtn" class="position-absolute bottom-0 end-0 bg-success text-white p-2 rounded-circle shadow" style="cursor: pointer; display: none;" title="Đổi ảnh đại diện">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                            <input type="file" id="avatarInput" name="avatar" class="d-none" accept="image/*" disabled>
                        </div>
                        <small class="text-muted d-block mt-2" id="avatarHint" style="display: none;">Dung lượng tối đa 2MB (JPG, PNG)</small>
                    </div>

                    <!-- Thông tin -->
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Email (Không thể thay đổi):</label>
                            <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Họ và tên:</label>
                            <input type="text" name="full_name" id="inputName" class="form-control rounded-3 bg-light" value="<?= htmlspecialchars($displayName) ?>" readonly required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Số điện thoại:</label>
                            <input type="text" name="phone" id="inputPhone" class="form-control rounded-3 bg-light" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Chưa cập nhật" readonly>
                        </div>
                    </div>
                </div>

                <!-- Nút lưu / hủy ẩn mặc định -->
                <div class="d-flex justify-content-end gap-2 border-top pt-3" id="actionButtons" style="display: none !important;">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-3" onclick="cancelEdit()">Hủy bỏ</button>
                    <button type="submit" name="btn_update_profile" class="btn btn-success px-4 rounded-3 fw-bold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleEditMode() {
        // Mở khóa input
        document.getElementById('inputName').removeAttribute('readonly');
        document.getElementById('inputName').classList.remove('bg-light');

        document.getElementById('inputPhone').removeAttribute('readonly');
        document.getElementById('inputPhone').classList.remove('bg-light');

        // Mở khóa file input avatar
        document.getElementById('avatarInput').removeAttribute('disabled');

        // Hiện nút camera và hướng dẫn ảnh
        document.getElementById('cameraBtn').style.display = 'block';
        document.getElementById('avatarHint').style.display = 'block';

        // Hiện cụm nút Lưu / Hủy và ẩn nút Sửa
        document.getElementById('actionButtons').style.setProperty('display', 'flex', 'important');
        document.getElementById('btnEnableEdit').style.display = 'none';
    }

    function cancelEdit() {
        // Khóa lại như cũ và reload lại trang hoặc reset form
        location.reload();
    }

    // Preview ảnh trước khi lưu
    document.getElementById('avatarInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>