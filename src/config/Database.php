<?php

namespace App\config;

use PDO;
use PDOException;

class Database
{
    protected string $dbname = 'u374195687_samatif';
    protected string $host = 'localhost';
    protected string $user = 'u374195687_samatif';
    protected string $password = 'Samatif2024';

    public function getConnection(): PDO
    {
      $dsn = "mysql:host={$this->host};dbname={$this->dbname}";
      try {
        $connection = new PDO($dsn, $this->user, $this->password);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $connection;
      } catch (PDOException $e) {
        die($e->getMessage());
      }
    }
}