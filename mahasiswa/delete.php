<?php
include '../koneksi.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = isset($_POST['nim']) ? $_POST['nim'] : '';

    if (empty($nim)) {
        $response = array('status' => 'error', 'message' => 'NIM tidak boleh kosong.');
    } else {
        try {
            $query = "DELETE FROM mahasiswa WHERE nim = :nim";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nim', $nim, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $response = array('status' => 'success', 'message' => 'Data mahasiswa berhasil dihapus.');
            } else {
                $response = array('status' => 'error', 'message' => 'Gagal menghapus data mahasiswa.');
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
