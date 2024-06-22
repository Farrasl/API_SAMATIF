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

// Fungsi untuk mendapatkan data dosen dan mahasiswa berdasarkan NIM
function getDosenMahasiswa($conn, $nim = null) {
    if ($nim) {
        $query_mahasiswa = "SELECT m.NIM, m.Nama, m.Semester, d.nip, d.nama AS nama_dosen
                            FROM mahasiswa m
                            INNER JOIN riwayat_pa r ON m.NIM = r.NIM
                            INNER JOIN dosen d ON r.NIP = d.nip
                            WHERE m.NIM = :nim";
    } else {
        $query_dosen = "SELECT nip, nama FROM dosen";
    }

    $data_mahasiswa = array();
    $data_dosen = array();

    try {
        if ($nim) {
            $stmt_mahasiswa = $conn->prepare($query_mahasiswa);
            $stmt_mahasiswa->bindParam(':nim', $nim);
            $stmt_mahasiswa->execute();
            $result_mahasiswa = $stmt_mahasiswa->fetchAll(PDO::FETCH_ASSOC);

            if ($result_mahasiswa) {
                foreach ($result_mahasiswa as $mahasiswa) {
                    $data_mahasiswa[] = array(
                        'Nama Mahasiswa' => $mahasiswa['Nama'],
                        'NIM' => $mahasiswa['NIM'],
                        'Semester' => $mahasiswa['Semester'],
                        'Nama Dosen PA' => $mahasiswa['nama_dosen'],
                        'NIP Dosen PA' => $mahasiswa['nip']
                    );
                }
                return array('status' => 'success', 'mahasiswa' => $data_mahasiswa);
            } else {
                return array('status' => 'error', 'message' => 'Tidak ada data mahasiswa yang ditemukan.');
            }
        } else {
            // Query untuk mendapatkan data dosen
            $stmt_dosen = $conn->prepare($query_dosen);
            $stmt_dosen->execute();
            $result_dosen = $stmt_dosen->fetchAll(PDO::FETCH_ASSOC);

            if ($result_dosen) {
                foreach ($result_dosen as $dosen) {
                    $nip = $dosen['nip'];
                    $nama_dosen = $dosen['nama'];

                    // Query untuk mendapatkan mahasiswa yang diampu oleh dosen
                    $query_mahasiswa = "SELECT m.NIM, m.Nama, m.Semester
                                        FROM mahasiswa m
                                        INNER JOIN riwayat_pa r ON m.NIM = r.NIM
                                        INNER JOIN dosen d ON r.NIP = d.nip
                                        WHERE d.nip = :nip";

                    $stmt_mahasiswa = $conn->prepare($query_mahasiswa);
                    $stmt_mahasiswa->bindParam(':nip', $nip);
                    $stmt_mahasiswa->execute();
                    $result_mahasiswa = $stmt_mahasiswa->fetchAll(PDO::FETCH_ASSOC);

                    $data_dosen[] = array(
                        'Nama' => $nama_dosen,
                        'NIP' => $nip,
                        'Mahasiswa' => $result_mahasiswa
                    );
                }
                return array('status' => 'success', 'dosen' => $data_dosen);
            } else {
                return array('status' => 'error', 'message' => 'Tidak ada data dosen yang ditemukan.');
            }
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
        if (isset($_GET['nim'])) {
            $nim = $_GET['nim'];
            $data_json = getDosenMahasiswa($conn, $nim);
        } else {
            $data_json = array('status' => 'error', 'message' => 'Input NIM Terlebih Dahulu');
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
