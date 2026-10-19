<?php

namespace App\Controllers;

use Core\Controller;

abstract class BaseController extends Controller
{
    protected function csrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    protected function hasValidCsrfToken($token)
    {
        return isset($_SESSION['csrf_token'])
            && is_string($token)
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function dispatch($action, $id = null)
    {
        if (!method_exists($this, $action) || $action === 'dispatch') {
            http_response_code(404);
            echo '404 - Không tìm thấy trang';
            return;
        }

        $this->{$action}($id);
    }

}
