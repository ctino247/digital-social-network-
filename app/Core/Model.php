<?php

namespace App\Core;

use PDO;

class Model
{
    protected static ?PDO $db = null;

    public function __construct()
    {
        if (self::$db === null) {
            self::$db = Database::connect();
        }
    }

    protected function db(): PDO
    {
        if (self::$db === null) {
            self::$db = Database::connect();
        }
        return self::$db;
    }

    public static function getDb(): PDO
    {
        if (self::$db === null) {
            self::$db = Database::connect();
        }
        return self::$db;
    }

    // Common query builder helper
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch(string $sql, array $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    public function lastInsertId(): string
    {
        return $this->db()->lastInsertId();
    }
}
