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
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$response = array();

// Validasi token sebelum mengambil data
if (isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION'] != '') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    list(, $token) = explode(' ', $authHeader);

    try {
        $decoded = JWT::decode($token, new Key(AppJwt::JWT_SECRET, 'HS256')); // Gunakan kunci yang sama seperti saat encoding

        // Token valid, lanjutkan dengan pengambilan data
        if (isset($_GET['nim'])) {
            $nim = $_GET['nim'];

            $sql_check_nim = "SELECT COUNT(*) FROM mahasiswa WHERE NIM = :nim";
            $stmt_check_nim = $conn->prepare($sql_check_nim);
            $stmt_check_nim->bindParam(':nim', $nim, PDO::PARAM_STR);
            $stmt_check_nim->execute();
            $nim_exists = $stmt_check_nim->fetchColumn();

            if ($nim_exists == 0) {
                $response = array('status' => 'error', 'message' => 'Mahasiswa dengan NIM tersebut tidak ditemukan.');
            } else {
                $sql_setoran = "SELECT i.id_setoran, m.Nama AS Nama_Mahasiswa, s.nama AS nama_surah, i.tanggal, i.kelancaran, i.tajwid, i.makhrajul_huruf
                                FROM setoran rs
                                JOIN setoran i ON rs.id_setoran = i.id_setoran
                                JOIN riwayat_pa pa ON rs.NIM = pa.NIM
                                JOIN surah s ON i.id_surah = s.id_surah
                                JOIN mahasiswa m ON rs.NIM = m.NIM
                                WHERE m.NIM = :nim
                                ORDER BY s.id_surah ASC"; // Menambahkan ORDER BY untuk mengurutkan berdasarkan ID surah secara ascending

                $stmt_setoran = $conn->prepare($sql_setoran);
                $stmt_setoran->bindParam(':nim', $nim, PDO::PARAM_STR);
                $stmt_setoran->execute();

                $setoran_list = array();

                if ($stmt_setoran->rowCount() > 0) {
                    while ($row = $stmt_setoran->fetch(PDO::FETCH_ASSOC)) {
                        $setoran_list[] = $row;
                    }
                }

                $response = array(
                    'status' => 'success',
                    'NIM' => $nim,
                    'setoran' => $setoran_list
                );
            }
        } else {
            $response = array('status' => 'error', 'message' => 'Inputkan NIM Terlebih Dahulu');
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
