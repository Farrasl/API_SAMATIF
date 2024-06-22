<?php
include '../koneksi.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$data_json = array();

try {
    if (isset($_GET['nama'])) {
        $nama = $_GET['nama'];
        $query = "SELECT * FROM dosen WHERE nama = :nama";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nama', $nama, PDO::PARAM_STR);
    } else {
        $query = "SELECT * FROM dosen";
        $stmt = $conn->prepare($query);
    }

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        $data_json = $result;
    } else {
        $data_json["error"] = "Dosen dengan Nama tersebut tidak ditemukan.";
    }
} catch(PDOException $e) {
    $data_json["error"] = "Query gagal: " . $e->getMessage();
}

echo json_encode($data_json, JSON_PRETTY_PRINT);

$conn = null;   
?>
