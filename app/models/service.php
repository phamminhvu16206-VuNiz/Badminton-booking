<?php
// File: app/models/service.php
namespace App\Models;

use App\Core\Database;

class Service {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllServices() {
        return $this->db->query("SELECT * FROM services ORDER BY id ASC")->fetchAll();
    }
}