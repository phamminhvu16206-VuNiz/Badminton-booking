<?php
// File: app/models/court.php
namespace App\Models;

class Court extends BaseModel
{
    public function getAllCourts()
    {
        return $this->db->query("SELECT * FROM courts ORDER BY id ASC")->fetchAll();
    }

    public function getFeaturedCourts($limit = 6)
    {
        $limit = max(1, min(24, (int) $limit));
        return $this->db->query("SELECT * FROM courts WHERE status = 'active' LIMIT {$limit}")->fetchAll();
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

    public function searchCourts($keyword = '', $priceFilter = '', $status = '')
    {
        $sql = 'SELECT * FROM courts WHERE 1 = 1';
        $parameters = [];
        if ($keyword !== '') {
            $sql .= ' AND (name LIKE ? OR address LIKE ?)';
            $parameters[] = '%' . $keyword . '%';
            $parameters[] = '%' . $keyword . '%';
        }
        if ($priceFilter === 'low') {
            $sql .= ' AND price < 120000';
        } elseif ($priceFilter === 'medium') {
            $sql .= ' AND price BETWEEN 120000 AND 150000';
        } elseif ($priceFilter === 'high') {
            $sql .= ' AND price > 150000';
        }
        if ($status !== '') {
            $sql .= ' AND status = ?';
            $parameters[] = $status;
        }
        $sql .= ' ORDER BY id DESC';
        return $this->statement($sql, $parameters)->fetchAll();
    }

    public function deleteCourt($id)
    {
        return $this->statement('DELETE FROM courts WHERE id = ?', [$id])->rowCount() > 0;
    }

    public function saveAdminCourt($name, $price, $description, $status = 'active', $id = null)
    {
        if (!in_array($status, ['active', 'maintenance'], true)) {
            return false;
        }

        $columns = $this->db->query("SHOW COLUMNS FROM courts")->fetchAll(\PDO::FETCH_COLUMN);
        $priceColumn = in_array('price_per_hour', $columns, true) ? 'price_per_hour' : 'price';
        if ($id === null) {
            return $this->statement("INSERT INTO courts (name, {$priceColumn}, description, status) VALUES (?, ?, ?, ?)", [$name, $price, $description, $status])->rowCount() > 0;
        }
        return $this->statement("UPDATE courts SET name = ?, {$priceColumn} = ?, description = ?, status = ? WHERE id = ?", [$name, $price, $description, $status, $id])->rowCount() > 0;
    }
}
