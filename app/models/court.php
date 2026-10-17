<?php
// File: app/models/court.php
namespace App\Models;

use App\Core\Database;

class Court
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllCourts()
    {
        return $this->db->query("SELECT * FROM courts ORDER BY id ASC")->fetchAll();
    }

    public function getCourtById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM courts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateCourt($id, $name, $price, $description)
    {
        $stmt = $this->db->prepare("UPDATE courts SET name = ?, price = ?, description = ? WHERE id = ?");
        return $stmt->execute([$name, $price, $description, $id]);
    }

    public function createCourt($name, $price, $description)
    {
        $stmt = $this->db->prepare("INSERT INTO courts (name, price, description) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $price, $description]);
    }
}
