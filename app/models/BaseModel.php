<?php

namespace App\Models;

use App\Core\Database;

abstract class BaseModel
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    protected function statement($sql, array $parameters = [])
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);
        return $statement;
    }
}
