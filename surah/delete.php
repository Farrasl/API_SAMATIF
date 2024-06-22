<?php
include '../koneksi.php';

header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_surah = isset($_POST['id_surah']) ? $_POST['id_surah'] : '';

    if (empty($id_surah)) {
        $response = array('status' => 'error', 'message' => 'ID surah harus diisi.');
    } else {
        try {
            $query = "DELETE FROM surah WHERE id_surah = :id_surah";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id_surah', $id_surah, PDO::PARAM_INT);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    $response = array('status' => 'success', 'message' => 'Data surah berhasil dihapus.');
                } else {
                    $response = array('status' => 'error', 'message' => 'ID surah tidak ditemukan.');
                }
            } else {
                $response = array('status' => 'error', 'message' => 'Gagal menghapus data surah.');
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
