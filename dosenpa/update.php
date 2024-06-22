<?php
include '../koneksi.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = isset($_POST['nim']) ? $_POST['nim'] : '';
    $nip_pa_baru = isset($_POST['nip_pa_baru']) ? $_POST['nip_pa_baru'] : '';
    $status_baru = isset($_POST['status_baru']) ? $_POST['status_baru'] : 'Aktif';

    if (empty($nim) || empty($nip_pa_baru)) {
        $response = array('status' => 'error', 'message' => 'NIM dan NIP PA baru harus diisi.');
    } else {
        try {
            $query_check_nim = "SELECT COUNT(*) FROM mahasiswa WHERE NIM = :nim";
            $stmt_check_nim = $conn->prepare($query_check_nim);
            $stmt_check_nim->bindParam(':nim', $nim, PDO::PARAM_STR);
            $stmt_check_nim->execute();
            $nim_exists = $stmt_check_nim->fetchColumn();

            if ($nim_exists == 0) {
                $response = array('status' => 'error', 'message' => 'NIM tidak ditemukan di tabel mahasiswa.');
            } else {
                $conn->beginTransaction();

                $query_update_pa_sebelumnya = "UPDATE riwayat_pa SET status = 'Tidak Aktif' WHERE NIM = :nim AND status = 'Aktif'";
                $stmt_update_pa_sebelumnya = $conn->prepare($query_update_pa_sebelumnya);
                $stmt_update_pa_sebelumnya->bindParam(':nim', $nim, PDO::PARAM_STR);
                $stmt_update_pa_sebelumnya->execute();

                $query_update_pa_baru = "INSERT INTO riwayat_pa (NIM, NIP, status) VALUES (:nim, :nip_pa_baru, :status_baru)";
                $stmt_update_pa_baru = $conn->prepare($query_update_pa_baru);
                $stmt_update_pa_baru->bindParam(':nim', $nim, PDO::PARAM_STR);
                $stmt_update_pa_baru->bindParam(':nip_pa_baru', $nip_pa_baru, PDO::PARAM_STR);
                $stmt_update_pa_baru->bindParam(':status_baru', $status_baru, PDO::PARAM_STR);

                if ($stmt_update_pa_baru->execute()) {
                    $conn->commit();
                    $response = array('status' => 'success', 'message' => 'Dosen PA berhasil diperbarui dan PA sebelumnya dinonaktifkan.');
                } else {
                    $conn->rollBack();
                    $response = array('status' => 'error', 'message' => 'Gagal memperbarui dosen PA.');
                }
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            $response = array('status' => 'error', 'message' => 'Query gagal: ' . $e->getMessage());
        }
    }
} else {
    $response = array('status' => 'error', 'message' => 'Hanya metode POST yang diizinkan.');
}

echo json_encode($response, JSON_PRETTY_PRINT);

$conn = null;
?>
