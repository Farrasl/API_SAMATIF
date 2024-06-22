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

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Validasi token sebelum mengambil data
    if (isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION'] != '') {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        list(, $token) = explode(' ', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key(AppJwt::JWT_SECRET, 'HS256')); // Gunakan kunci yang sama seperti saat encoding

            // Token valid, lanjutkan dengan pengambilan data
            if (isset($_GET['nim'])) {
                $nim = $_GET['nim'];

                if (empty($nim)) {
                    $response = array('status' => 'error', 'message' => 'NIM tidak boleh kosong.');
                } else {
                    try {
                        $query_mahasiswa = "SELECT Nama FROM mahasiswa WHERE NIM = :nim";
                        $stmt_mahasiswa = $conn->prepare($query_mahasiswa);
                        $stmt_mahasiswa->bindParam(':nim', $nim, PDO::PARAM_STR);
                        $stmt_mahasiswa->execute();
                        $result_mahasiswa = $stmt_mahasiswa->fetch(PDO::FETCH_ASSOC);

                        if ($result_mahasiswa) {
                            $nama_mahasiswa = $result_mahasiswa['Nama'];

                            // Definisikan range ID surah untuk setiap langkah keberhasilan
                            $langkahs = array(
                                "Kerja Praktek" => range(1, 8), // An-Naba’ sampai Al-Buruj
                                "Seminar Kerja Praktek" => range(9, 16), // Ath-Thaariq sampai Adh-Dhuha
                                "Judul Tugas Akhir" => range(17, 22), // Al-Insyirah sampai Az-Zalzalah
                                "Seminar Proposal" => range(23, 34), // Al-Aadiyaat sampai Al-Lahab
                                "Sidang Tugas Akhir" => range(35, 37) // Al-Ikhlash sampai An-Naas
                            );

                            $percentages = array();

                            foreach ($langkahs as $langkah => $range) {
                                // Query untuk menghitung jumlah setoran pada range ID surah yang ditentukan
                                $query_count = "SELECT COUNT(*) AS count FROM setoran s
                                                JOIN surah sur ON s.id_surah = sur.id_surah
                                                WHERE s.NIM = :nim AND sur.id_surah IN (" . implode(",", $range) . ")";
                                $stmt_count = $conn->prepare($query_count);
                                $stmt_count->bindParam(':nim', $nim, PDO::PARAM_STR);
                                $stmt_count->execute();
                                $result_count = $stmt_count->fetch(PDO::FETCH_ASSOC);
                                $percent = ($result_count['count'] / count($range)) * 100;

                                // Ambil nama surah dari range ID surah yang ditentukan
                                $query_surah_names = "SELECT nama FROM surah WHERE id_surah IN (" . implode(",", $range) . ")";
                                $stmt_surah_names = $conn->prepare($query_surah_names);
                                $stmt_surah_names->execute();
                                $surah_names = $stmt_surah_names->fetchAll(PDO::FETCH_COLUMN);

                                $percentages[] = array('lang' => $langkah, 'percent' => $percent, 'surah_names' => $surah_names);
                            }

                            $response = array(
                                'status' => 'success',
                                'Nama' => $nama_mahasiswa,
                                'NIM' => $nim,
                                'percentages' => $percentages
                            );
                        } else {
                            $response = array('status' => 'error', 'message' => 'Mahasiswa dengan NIM tersebut tidak ditemukan.');
                        }
                    } catch (PDOException $e) {
                        $response = array('status' => 'error', 'message' => 'Query gagal: ' . $e->getMessage());
                    }
                }
            } else {
                $response = array('status' => 'error', 'message' => 'NIM tidak boleh kosong.');
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
} else {
    $response = array('status' => 'error', 'message' => 'Hanya metode GET yang diizinkan.');
}

echo json_encode($response, JSON_PRETTY_PRINT);

$conn = null;
?>
