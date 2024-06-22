<?php
include '../koneksi.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = isset($_POST['nim']) ? trim($_POST['nim']) : '';
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';

    if (empty($nim) || empty($nama) || empty($semester)) {
        $response = array('status' => 'error', 'message' => 'NIM, Nama, dan Semester tidak boleh kosong.');
    } else {
        try {
            $query_check = "SELECT COUNT(*) FROM mahasiswa WHERE nim = :nim";
            $stmt_check = $conn->prepare($query_check);
            $stmt_check->bindParam(':nim', $nim, PDO::PARAM_STR);
            $stmt_check->execute();
            $nim_exists = $stmt_check->fetchColumn();

            if ($nim_exists == 0) {
                $response = array('status' => 'error', 'message' => 'NIM tidak ditemukan.');
            } else {
                $query_update = "UPDATE mahasiswa SET nama = :nama, semester = :semester WHERE nim = :nim";
                $stmt_update = $conn->prepare($query_update);
                $stmt_update->bindParam(':nim', $nim, PDO::PARAM_STR);
                $stmt_update->bindParam(':nama', $nama, PDO::PARAM_STR);
                $stmt_update->bindParam(':semester', $semester, PDO::PARAM_STR);

                if ($stmt_update->execute()) {
                    $response = array('status' => 'success', 'message' => 'Data mahasiswa berhasil diperbarui.');
                } else {
                    $response = array('status' => 'error', 'message' => 'Gagal memperbarui data mahasiswa.');
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
