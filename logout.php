<?php
// File: logout.php
// Chức năng: Xóa phiên đăng nhập của người dùng / admin và chuyển hướng về trang đăng nhập

session_start();

// Xóa toàn bộ biến Session
$_SESSION = array();

// Nếu muốn xóa sạch Session Cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy hoàn toàn session
session_destroy();

// Khởi tạo lại session mới để gửi thông báo chuyển hướng
session_start();
$_SESSION['success'] = 'Bạn đã đăng xuất thành công!';

// Chuyển hướng về trang đăng nhập
header("Location: login.php");
exit();
?>