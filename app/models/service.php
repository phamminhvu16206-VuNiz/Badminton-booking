<?php
// File: app/models/service.php
namespace App\Models;

class Service extends BaseModel {

    public function getAllServices() {
        return $this->db->query("SELECT * FROM services ORDER BY id ASC")->fetchAll();
    }

    public function create($name, $price, $unit) {
        return (bool) $this->statement('INSERT INTO services (name, price, unit) VALUES (?, ?, ?)', [$name, $price, $unit]);
    }

    public function update($id, $name, $price, $unit) {
        return (bool) $this->statement('UPDATE services SET name = ?, price = ?, unit = ? WHERE id = ?', [$name, $price, $unit, $id]);
    }

    public function delete($id) {
        return (bool) $this->statement('DELETE FROM services WHERE id = ?', [$id]);
    }
}