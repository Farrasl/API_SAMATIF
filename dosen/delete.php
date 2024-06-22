<?php
include '../koneksi.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = isset($_POST['nip']) ? $_POST['nip'] : '';

    if (empty($nip)) {
        $response = array('status' => 'error', 'message' => 'NIP tidak boleh kosong.');
    } else {
        try {
            $query = "DELETE FROM dosen WHERE nip = :nip";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nip', $nip, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $response = array('status' => 'success', 'message' => 'Data dosen berhasil dihapus.');
            } else {
                $response = array('status' => 'error', 'message' => 'Gagal menghapus data dosen.');
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
