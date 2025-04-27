<?php

namespace App\Services;

use PDO;
use PDOException;

class DatabaseConnection
{
    public static function connect(): PDO
    {
        $host = "c724r43q8jp5nk.cluster-czz5s0kz4scl.eu-west-1.rds.amazonaws.com";
        $database = "d4nk1746t10jil";
        $user = "uk0klbg1uescn";
        $password = "p4408a7b7ac517dbb23e0ff811487d6f0c68576d627ea50312b0c9835cd3e78b5";
        $port = 5432;

        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
            return new PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
}
