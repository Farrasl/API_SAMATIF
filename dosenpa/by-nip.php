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

// Fungsi untuk mendapatkan data dosen dan mahasiswa berdasarkan NIP
function getDosenMahasiswa($conn, $nip = null) {
    if (!$nip) {
        return array('status' => 'error', 'message' => 'Input NIP Terlebih Dahulu');
    }

    $query_dosen = "SELECT d.nip, d.nama AS nama_dosen, m.NIM, m.Nama, m.Semester
                    FROM dosen d
                    INNER JOIN riwayat_pa r ON d.nip = r.NIP
                    INNER JOIN mahasiswa m ON r.NIM = m.NIM
                    WHERE d.nip = :nip";

    try {
        $stmt_dosen = $conn->prepare($query_dosen);
        $stmt_dosen->bindParam(':nip', $nip);
        $stmt_dosen->execute();
        $result_dosen = $stmt_dosen->fetchAll(PDO::FETCH_ASSOC);

        if ($result_dosen) {
            $data_dosen = array();
            $currentDosen = null;

            foreach ($result_dosen as $row) {
                if (!$currentDosen) {
                    $currentDosen = array(
                        'NIP' => $row['nip'],
                        'Nama' => $row['nama_dosen'],
                        'Mahasiswa' => array()
                    );
                }

                if ($row['NIM']) {
                    $currentDosen['Mahasiswa'][] = array(
                        'NIM' => $row['NIM'],
                        'Nama' => $row['Nama'],
                        'Semester' => $row['Semester']
                    );
                }
            }

            if ($currentDosen) {
                $data_dosen[] = $currentDosen;
            }

            return array('dosen' => $data_dosen);
        } else {
            return array('status' => 'error', 'message' => 'Tidak ada data dosen yang ditemukan.');
        }
    } catch(PDOException $e) {
        return array('status' => 'error', 'message' => 'Query gagal: ' . $e->getMessage());
    }
}

// Validasi token sebelum mengambil data
if (isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION'] != '') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    list(, $token) = explode(' ', $authHeader);

    try {
        $decoded = JWT::decode($token, new Key(AppJwt::JWT_SECRET, 'HS256')); // Gunakan kunci yang sama seperti saat encoding

        // Token valid, lanjutkan dengan pengambilan data
        if (isset($_GET['nip'])) {
            $nip = $_GET['nip'];
            $data_json = getDosenMahasiswa($conn, $nip);
        } else {
            $data_json = array('status' => 'error', 'message' => 'Input NIP Terlebih Dahulu');
        }
    } catch (ExpiredException $e) {
        http_response_code(401);
        $data_json = array('status' => 'error', 'message' => 'Token expired');
    } catch (SignatureInvalidException $e) {
        http_response_code(401);
        $data_json = array('status' => 'error', 'message' => 'Token signature invalid');
    } catch (Exception $e) {
        http_response_code(401);
        $data_json = array('status' => 'error', 'message' => 'Token tidak valid: ' . $e->getMessage());
    }
} else {
    // Jika token tidak ditemukan, berikan respons error
    $data_json = array('status' => 'error', 'message' => 'Login Terlebih dahulu');
}

echo json_encode($data_json, JSON_PRETTY_PRINT);

$conn = null;
?>
