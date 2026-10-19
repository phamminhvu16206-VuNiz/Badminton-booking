<?php

namespace App\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Service;
use App\Models\User;

class AdminController extends BaseController
{
    public function bookings($id = null)
    {
        $this->requireAdmin();
        $success = '';
        $error = '';
        if (isset($_GET['action'], $_GET['id'])) {
            $bookingId = (int) $_GET['id'];
            $status = $_GET['action'] === 'confirm' ? 'confirmed' : ($_GET['action'] === 'cancel' ? 'cancelled' : '');
            if ($status !== '') {
                try {
                    if ((new Booking())->updateStatus($bookingId, $status)) {
                        $success = $status === 'confirmed' ? 'Đã duyệt đơn đặt sân thành công!' : 'Đã hủy đơn đặt sân thành công!';
                    } else {
                        $error = 'Không thể cập nhật đơn đặt sân, vui lòng thử lại.';
                    }
                } catch (\PDOException $exception) {
                    $error = 'Không thể cập nhật đơn đặt sân: ' . $exception->getMessage();
                }
            }
        }

        $all_bookings = (new Booking())->getAllBookings();
        $this->render('admin/bookings', compact('success', 'error', 'all_bookings'));
    }

    public function cancellationRequests($id = null)
    {
        $this->requireAdmin();
        $success = $_SESSION['cancellation_success'] ?? '';
        $error = $_SESSION['cancellation_error'] ?? '';
        unset($_SESSION['cancellation_success'], $_SESSION['cancellation_error']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_refund'])) {
            if (!$this->hasValidCsrfToken($_POST['csrf_token'] ?? null)) {
                $_SESSION['cancellation_error'] = 'Phiên xác nhận không hợp lệ. Vui lòng tải lại trang và thử lại.';
            } elseif ((int) ($_POST['booking_id'] ?? 0) <= 0) {
                $_SESSION['cancellation_error'] = 'Mã đơn đặt sân không hợp lệ.';
            } elseif ((new Booking())->confirmCancellationRefund((int) $_POST['booking_id'])) {
                $_SESSION['cancellation_success'] = 'Đã xác nhận hoàn tiền và cập nhật đơn sang trạng thái đã hủy.';
            } else {
                $_SESSION['cancellation_error'] = 'Yêu cầu không còn ở trạng thái chờ xác nhận hoặc đã được xử lý.';
            }

            header('Location: ' . APP_BASE_URL . '/admin/cancellation-requests');
            exit;
        }

        $requests = (new Booking())->getCancellationRequests();
        $csrfToken = $this->csrfToken();
        $this->render('admin/cancellation-requests', compact('success', 'error', 'requests', 'csrfToken'));
    }

    public function courts($id = null)
    {
        $this->requireAdmin();
        $courtModel = new Court();
        $success = '';
        $error = '';

        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            try {
                if ($courtModel->deleteCourt((int) ($_GET['id'] ?? 0))) {
                    $success = 'Xóa sân thành công!';
                } else {
                    $error = 'Thất bại! Không thể xóa sân.';
                }
            } catch (\PDOException $exception) {
                $error = 'Không thể xóa sân này vì đã có lịch đặt liên quan!';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['btn_update_court']) || isset($_POST['btn_add_court']))) {
            $name = trim($_POST['name'] ?? '');
            $price = (float) ($_POST['price'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $status = $_POST['status'] ?? 'active';
            if ($price <= 0 || $name === '' || !in_array($status, ['active', 'maintenance'], true)) {
                $error = 'Tên sân, giá thuê hoặc trạng thái không hợp lệ!';
            } else {
                $courtId = isset($_POST['btn_update_court']) ? (int) ($_POST['court_id'] ?? 0) : null;
                if ($courtModel->saveAdminCourt($name, $price, $description, $status, $courtId)) {
                    $success = $courtId === null ? 'Thêm sân mới thành công!' : 'Cập nhật thông tin sân và trạng thái thành công!';
                } else {
                    $error = $courtId === null ? 'Thêm sân thất bại!' : 'Không có thay đổi hoặc cập nhật sân thất bại!';
                }
            }
        }

        $courts = $courtModel->getAllCourts();
        $this->render('admin/courts', compact('success', 'error', 'courts'));
    }

    public function services($id = null)
    {
        $this->requireAdmin();
        $serviceModel = new Service();
        $success = '';
        $error = '';

        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            if ($serviceModel->delete((int) ($_GET['id'] ?? 0))) {
                $success = 'Xóa dịch vụ thành công!';
            } else {
                $error = 'Xóa dịch vụ thất bại!';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['btn_update_service']) || isset($_POST['btn_add_service']))) {
            $name = trim($_POST['name'] ?? '');
            $price = (float) ($_POST['price'] ?? -1);
            $unit = trim($_POST['unit'] ?? '');
            if ($name === '' || $price < 0) {
                $error = 'Tên dịch vụ và giá không hợp lệ!';
            } elseif (isset($_POST['btn_update_service'])) {
                if ($serviceModel->update((int) ($_POST['service_id'] ?? 0), $name, $price, $unit)) {
                    $success = 'Cập nhật dịch vụ thành công!';
                } else {
                    $error = 'Có lỗi xảy ra!';
                }
            } elseif ($serviceModel->create($name, $price, $unit)) {
                $success = 'Thêm dịch vụ mới thành công!';
            } else {
                $error = 'Thêm thất bại!';
            }
        }

        $services = $serviceModel->getAllServices();
        $this->render('admin/services', compact('success', 'error', 'services'));
    }

    public function users($id = null)
    {
        $this->requireAdmin();
        $userModel = new User();
        $success = '';
        $error = '';

        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            $deleteId = (int) ($_GET['id'] ?? 0);
            if ($deleteId === (int) $_SESSION['user_id']) {
                $error = 'Không thể tự xóa tài khoản admin đang đăng nhập.';
            } elseif ($userModel->deleteUser($deleteId)) {
                $success = 'Xóa người dùng thành công!';
            } else {
                $error = 'Xóa người dùng thất bại!';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_add_user'])) {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            if ($name === '' || $email === '') {
                $error = 'Vui lòng nhập đầy đủ tên và email!';
            } elseif ($userModel->emailExists($email)) {
                $error = 'Email này đã tồn tại trong hệ thống!';
            } else {
                $password = $_POST['password'] ?? '';
                $created = $userModel->createAdminUser([
                    'name' => $name,
                    'email' => $email,
                    'phone' => trim($_POST['phone'] ?? ''),
                    'role' => $_POST['role'] ?? 'user',
                    'password' => password_hash($password !== '' ? $password : '123456', PASSWORD_BCRYPT),
                ]);
                $success = $created ? 'Thêm người dùng mới thành công!' : '';
                $error = $created ? '' : 'Thêm thất bại!';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_update_user'])) {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            if ($name === '' || $email === '') {
                $error = 'Tên và email không được để trống!';
            } else {
                $password = $_POST['password'] ?? '';
                $updated = $userModel->updateAdminUser([
                    'id' => (int) ($_POST['user_id'] ?? 0),
                    'name' => $name,
                    'email' => $email,
                    'phone' => trim($_POST['phone'] ?? ''),
                    'role' => $_POST['role'] ?? 'user',
                    'password' => $password === '' ? '' : password_hash($password, PASSWORD_BCRYPT),
                ]);
                $success = $updated ? 'Cập nhật thông tin người dùng thành công!' : '';
                $error = $updated ? '' : 'Cập nhật thất bại!';
            }
        }

        $users = $userModel->getAllUsers();
        $this->render('admin/users', compact('success', 'error', 'users'));
    }

    private function requireAdmin()
    {
        $role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
        if (empty($_SESSION['user_id']) || $role !== 'admin') {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang quản trị.';
            header('Location: ' . APP_BASE_URL . '/auth/login');
            exit;
        }
    }
}
