<?php

namespace App\Application\Services;

use PDO;
use PDOException;

class DatabaseConnection
{
    public static function connect(): PDO
    {
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT');
        $database = getenv('DB_DATABASE');
        $user = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');

        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
            return new PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
}
