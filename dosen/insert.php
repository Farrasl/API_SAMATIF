<?php
include '../koneksi.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = isset($_POST['nip']) ? $_POST['nip'] : '';
    $nama = isset($_POST['nama']) ? $_POST['nama'] : '';

    if (empty($nip) || empty($nama)) {
        $response = array('status' => 'error', 'message' => 'NIP dan Nama tidak boleh kosong.');
    } else {
        try {
            $query = "INSERT INTO dosen (nip, nama) VALUES (:nip, :nama)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nip', $nip, PDO::PARAM_STR);
            $stmt->bindParam(':nama', $nama, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $response = array('status' => 'success', 'message' => 'Data dosen berhasil ditambahkan.');
            } else {
                $response = array('status' => 'error', 'message' => 'Gagal menambahkan data dosen.');
            }
        } catch (PDOException $e) {
            $response = array('status' => 'error', 'message' => 'Query gagal: ' . $e->getMessage());
        }
    }
} else {
    $response = array('status' => 'error', 'message' => 'Hanya metode POST yang diizinkan.');
}

echo json_encode($response, JSON_PRETTY_PRINT);

$conn = null;
?>
