<?php
include '../koneksi.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$data_json = array();

try {
        $query = "SELECT * FROM mahasiswa";
        $stmt = $conn->prepare($query);

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        $data_json = $result;
    } else {
        $data_json["error"] = "Tidak ada data yang ditemukan.";
    }
} catch(PDOException $e) {
    $data_json["error"] = "Query gagal: " . $e->getMessage();
}

echo json_encode($data_json, JSON_PRETTY_PRINT);

$conn = null;   
?>
