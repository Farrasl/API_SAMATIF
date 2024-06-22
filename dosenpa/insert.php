<?php
include '../koneksi.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = isset($_POST['nim']) ? $_POST['nim'] : '';
    $nama = isset($_POST['nama']) ? $_POST['nama'] : '';
    $semester = isset($_POST['semester']) ? $_POST['semester'] : '';
    $nip_pa = isset($_POST['nip_pa']) ? $_POST['nip_pa'] : '';
    $status_pa = isset($_POST['status']) ? $_POST['status'] : '';

    if (empty($nim) || empty($nama) || empty($semester) || empty($nip_pa) || empty($status_pa)) {
        $response = array('status' => 'error', 'message' => 'Semua field harus diisi.');
    } else {
        try {
            $query_check_nim = "SELECT NIM FROM mahasiswa WHERE NIM = :nim";
            $stmt_check_nim = $conn->prepare($query_check_nim);
            $stmt_check_nim->bindParam(':nim', $nim, PDO::PARAM_STR);
            $stmt_check_nim->execute();

            if ($stmt_check_nim->rowCount() > 0) {
                $response = array('status' => 'error', 'message' => 'NIM sudah ada.');
            } else {
                $query_mahasiswa = "INSERT INTO mahasiswa (NIM, Nama, Semester) VALUES (:nim, :nama, :semester)";
                $stmt_mahasiswa = $conn->prepare($query_mahasiswa);
                $stmt_mahasiswa->bindParam(':nim', $nim, PDO::PARAM_STR);
                $stmt_mahasiswa->bindParam(':nama', $nama, PDO::PARAM_STR);
                $stmt_mahasiswa->bindParam(':semester', $semester, PDO::PARAM_INT);

                if ($stmt_mahasiswa->execute()) {
                    $query_riwayat_pa = "INSERT INTO riwayat_pa (NIM, NIP, status) VALUES (:nim, :nip_pa, :status_pa)";
                    $stmt_riwayat_pa = $conn->prepare($query_riwayat_pa);
                    $stmt_riwayat_pa->bindParam(':nim', $nim, PDO::PARAM_STR);
                    $stmt_riwayat_pa->bindParam(':nip_pa', $nip_pa, PDO::PARAM_STR);
                    $stmt_riwayat_pa->bindParam(':status_pa', $status_pa, PDO::PARAM_STR);

                    if ($stmt_riwayat_pa->execute()) {
                        $response = array('status' => 'success', 'message' => 'Data mahasiswa berhasil ditambahkan.');
                    } else {
                        $response = array('status' => 'error', 'message' => 'Gagal menambahkan data riwayat PA.');
                    }
                } else {
                    $response = array('status' => 'error', 'message' => 'Gagal menambahkan data mahasiswa.');
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
