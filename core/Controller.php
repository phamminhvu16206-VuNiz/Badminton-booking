<?php

namespace Core;

class Controller
{
    protected function render($view, array $data = [])
    {
        $viewPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $view) . '.php';
        if (!is_file($viewPath)) {
            throw new \RuntimeException('Không tìm thấy view: ' . $view);
        }

        extract($data, EXTR_SKIP);
        require $viewPath;
    }

    protected function loadModel($model)
    {
        $class = 'App\\Models\\' . $model;
        if (!class_exists($class)) {
            throw new \RuntimeException('Không tìm thấy model: ' . $model);
        }

        return new $class();
    }
}
