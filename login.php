<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'vendor/autoload.php';

$user = new \App\app\User();

// Validasi apakah `$_GET['action']` terdefinisi
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Periksa apakah metode ada dalam kelas `User`
    if (method_exists($user, $action)) {
        $user->$action();
    } else {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['message' => 'Invalid action']);
    }
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['message' => 'Action not specified']);
}
?>
