<?php
include '../koneksi.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = isset($_POST['nip']) ? trim($_POST['nip']) : '';
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';

    if (empty($nip) || empty($nama)) {
        $response = array('status' => 'error', 'message' => 'NIP dan Nama tidak boleh kosong.');
    } else {
        try {
            $query_check = "SELECT COUNT(*) FROM dosen WHERE nip = :nip";
            $stmt_check = $conn->prepare($query_check);
            $stmt_check->bindParam(':nip', $nip, PDO::PARAM_STR);
            $stmt_check->execute();
            $nip_exists = $stmt_check->fetchColumn();

            if ($nip_exists == 0) {
                $response = array('status' => 'error', 'message' => 'NIP tidak ditemukan.');
            } else {
                $query = "UPDATE dosen SET nama = :nama WHERE nip = :nip";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':nama', $nama, PDO::PARAM_STR);
                $stmt->bindParam(':nip', $nip, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $response = array('status' => 'success', 'message' => 'Data dosen berhasil diperbarui.');
                } else {
                    $response = array('status' => 'error', 'message' => 'Gagal memperbarui data dosen.');
                }
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
