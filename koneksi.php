<?php
class Koneksi {
    private $servername = "localhost";
    private $username = "u374195687_samatif";
    private $password = "Samatif2024";
    private $dbname = "u374195687_samatif";
    public $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=$this->servername;dbname=$this->dbname", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Koneksi gagal: " . $e->getMessage();
        }
    }
}

$database = new Koneksi();
$conn = $database->conn;
?>
