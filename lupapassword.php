<?php
include './koneksi.php'; 

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Atau ganti '*' dengan domain spesifik jika Anda tahu dari mana request akan datang
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Memastikan hanya request POST atau OPTIONS yang diizinkan
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Memastikan hanya request POST yang diizinkan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Metode request tidak diizinkan.']);
    exit;
}

// Memeriksa data yang diterima dari request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Data username dan password diperlukan.']);
    exit;
}

$username = $data['username'];
$password = $data['password'];

try {
    // Periksa apakah username ada dalam tabel user
    $checkUserQuery = "SELECT * FROM user WHERE username = :username";
    $checkUserStmt = $conn->prepare($checkUserQuery);
    $checkUserStmt->bindParam(':username', $username);
    $checkUserStmt->execute();
    $user = $checkUserStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Username tidak ditemukan.']);
        exit;
    }

    // Update password user
    $updatePasswordQuery = "UPDATE user SET password = :password WHERE username = :username";
    $updatePasswordStmt = $conn->prepare($updatePasswordQuery);
    $updatePasswordStmt->bindParam(':password', $password); // Memasukkan password langsung (tanpa hashing)
    $updatePasswordStmt->bindParam(':username', $username);

    if ($updatePasswordStmt->execute()) {
        echo json_encode(['message' => 'Password berhasil direset!']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Gagal mereset password.']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}

// Tutup koneksi database
$conn = null;
?>
