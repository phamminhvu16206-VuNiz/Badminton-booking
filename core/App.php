<?php

namespace Core;

class App
{
    private $routes = [
        '' => ['App\\Controllers\\HomeController', 'index'],
        'home' => ['App\\Controllers\\HomeController', 'index'],
        'home/index' => ['App\\Controllers\\HomeController', 'index'],
        'home/courts' => ['App\\Controllers\\HomeController', 'courts'],
        'home/detail' => ['App\\Controllers\\HomeController', 'detail'],
        'auth/login' => ['App\\Controllers\\AuthController', 'login'],
        'auth/register' => ['App\\Controllers\\AuthController', 'register'],
        'auth/forgot-password' => ['App\\Controllers\\AuthController', 'forgotPassword'],
        'auth/logout' => ['App\\Controllers\\AuthController', 'logout'],
        'user/profile' => ['App\\Controllers\\UserController', 'profile'],
        'booking/index' => ['App\\Controllers\\BookingController', 'index'],
        'booking/mine' => ['App\\Controllers\\BookingController', 'mine'],
        'booking/webhook' => ['App\\Controllers\\BookingController', 'webhook'],
        'admin' => ['App\\Controllers\\AdminController', 'bookings'],
        'admin/index' => ['App\\Controllers\\AdminController', 'bookings'],
        'admin/bookings' => ['App\\Controllers\\AdminController', 'bookings'],
        'admin/cancellation-requests' => ['App\\Controllers\\AdminController', 'cancellationRequests'],
        'admin/courts' => ['App\\Controllers\\AdminController', 'courts'],
        'admin/services' => ['App\\Controllers\\AdminController', 'services'],
        'admin/users' => ['App\\Controllers\\AdminController', 'users'],
    ];

    private $legacyAliases = [
        'index.php' => ['App\\Controllers\\HomeController', 'index'],
        'courts.php' => ['App\\Controllers\\HomeController', 'courts'],
        'court-detail.php' => ['App\\Controllers\\HomeController', 'detail'],
        'booking.php' => ['App\\Controllers\\BookingController', 'index'],
        'my-bookings.php' => ['App\\Controllers\\BookingController', 'mine'],
        'login.php' => ['App\\Controllers\\AuthController', 'login'],
        'register.php' => ['App\\Controllers\\AuthController', 'register'],
        'forgot-password.php' => ['App\\Controllers\\AuthController', 'forgotPassword'],
        'logout.php' => ['App\\Controllers\\AuthController', 'logout'],
        'profile.php' => ['App\\Controllers\\UserController', 'profile'],
        'webhook.php' => ['App\\Controllers\\BookingController', 'webhook'],
        'admin/index.php' => ['App\\Controllers\\AdminController', 'bookings'],
        'admin/courts.php' => ['App\\Controllers\\AdminController', 'courts'],
        'admin/services.php' => ['App\\Controllers\\AdminController', 'services'],
        'admin/users.php' => ['App\\Controllers\\AdminController', 'users'],
    ];

    public function run()
    {
        $requestPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $basePath = defined('APP_BASE_URL') ? APP_BASE_URL : '';
        if ($basePath !== '' && strpos($requestPath, $basePath . '/') === 0) {
            $requestPath = substr($requestPath, strlen($basePath));
        } elseif ($basePath !== '' && $requestPath === $basePath) {
            $requestPath = '/';
        }
        $path = trim((string) ($_GET['url'] ?? $requestPath), '/');
        $path = preg_replace('#^public/?#', '', $path);
        $path = preg_replace('#\\.php$#i', '.php', $path);

        if (isset($this->legacyAliases[$path])) {
            [$controllerClass, $action] = $this->legacyAliases[$path];
            (new $controllerClass())->dispatch($action);
            return;
        }

        $segments = array_values(array_filter(explode('/', $path), 'strlen'));
        $id = null;
        if (count($segments) > 2 && ctype_digit((string) end($segments))) {
            $id = array_pop($segments);
        }
        if ($id !== null && !isset($_GET['id'])) {
            $_GET['id'] = $id;
        }
        $route = implode('/', array_map('strtolower', $segments));

        if (!isset($this->routes[$route])) {
            http_response_code(404);
            echo '404 - Không tìm thấy trang';
            return;
        }

        [$controllerClass, $action] = $this->routes[$route];
        $controller = new $controllerClass();
        $controller->dispatch($action, $id);
    }
}
