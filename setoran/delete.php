<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php'; // Autoload Composer dependencies
require '../src/config/AppJwt.php'; // Impor kelas AppJwt
include '../koneksi.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Exception;
use App\config\AppJwt;

header('Access-Control-Allow-Origin: *'); // Izinkan akses dari semua asal
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$response = array();

// Validasi token sebelum menghapus data
if (isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION'] != '') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    list(, $token) = explode(' ', $authHeader);

    try {
        $decoded = JWT::decode($token, new Key(AppJwt::JWT_SECRET, 'HS256')); // Gunakan kunci yang sama seperti saat encoding

        // Token valid, lanjutkan dengan penghapusan data
        if (isset($_POST['id_setoran'])) {
            $id_setoran = $_POST['id_setoran'];

            try {
                $sql_delete = "DELETE FROM setoran WHERE id_setoran = :id_setoran";
                $stmt_delete = $conn->prepare($sql_delete);
                $stmt_delete->bindParam(':id_setoran', $id_setoran, PDO::PARAM_INT);

                if ($stmt_delete->execute()) {
                    $response = array('status' => 'success', 'message' => 'Setoran berhasil dihapus.');
                } else {
                    $response = array('status' => 'error', 'message' => 'Gagal menghapus setoran.');
                }
            } catch (PDOException $e) {
                $response = array('status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage());
            }
        } else {
            $response = array('status' => 'error', 'message' => 'ID setoran tidak ditemukan.');
        }
    } catch (ExpiredException $e) {
        http_response_code(401);
        $response = array('status' => 'error', 'message' => 'Token expired');
    } catch (SignatureInvalidException $e) {
        http_response_code(401);
        $response = array('status' => 'error', 'message' => 'Token signature invalid');
    } catch (Exception $e) {
        http_response_code(401);
        $response = array('status' => 'error', 'message' => 'Token tidak valid: ' . $e->getMessage());
    }
} else {
    // Jika token tidak ditemukan, berikan respons error
    $response = array('status' => 'error', 'message' => 'Login Terlebih dahulu');
}

echo json_encode($response, JSON_PRETTY_PRINT);

$conn = null;
?>
