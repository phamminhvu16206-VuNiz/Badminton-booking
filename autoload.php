<?php
// File: autoload.php
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\' => __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR,
    ];

    foreach ($prefixes as $prefix => $baseDirectory) {
        if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
        $classParts = explode('\\', $relativeClass);
        $classFile = array_pop($classParts) . '.php';
        $lowercaseDirectories = array_map('strtolower', $classParts);
        $candidates = [
            $baseDirectory . $relativePath,
            $baseDirectory . implode(DIRECTORY_SEPARATOR, $lowercaseDirectories) . DIRECTORY_SEPARATOR . $classFile,
            $baseDirectory . strtolower($relativePath),
        ];

        foreach ($candidates as $file) {
            if (is_file($file)) {
                require_once $file;
                return;
            }
        }
    }
});