<?php
// File: autoload.php
spl_autoload_register(function ($class) {
    // Chuyển namespace "App\" thành "app/"
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    
    // Tách đường dẫn thư mục và tên file để chuyển tất cả về chữ viết thường
    $file = $base_dir . strtolower(str_replace('\\', '/', $relative_class)) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});