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
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$response = array();

if (isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION'] != '') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    list(, $token) = explode(' ', $authHeader);

    try {
        $decoded = JWT::decode($token, new Key(AppJwt::JWT_SECRET, 'HS256')); // Gunakan kunci yang sama seperti saat encoding

        // Token valid, lanjutkan dengan pengambilan data
        $query = "SELECT id_surah AS id, nama AS name FROM surah";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $response = $result;
        } else {
            $response = array('status' => 'error', 'message' => 'Tidak ada data surah ditemukan.');
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
    $response = array('status' => 'error', 'message' => 'Login Terlebih Dahulu');
}

echo json_encode($response, JSON_PRETTY_PRINT);

$conn = null;
?>
