<?php

namespace App\app;

use App\config\AppJwt;
use App\config\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class User
{
    protected \PDO $connection;

    public function __construct()
    {
        $db = new Database();
        $this->connection = $db->getConnection();
    }

    /**
     * Proses login via API
     * @return void
     */
    public function login()
    {
        header('Content-Type: application/json');
    
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
    
        // Validate input
        if (!isset($input['username']) || !isset($input['password'])) {
            echo json_encode(['message' => 'Username and password are required']);
            exit();
        }
    
        $username = $input['username'];
        $password = $input['password'];
    
        // Check if username is NIP (dosen) or NIM (mahasiswa)
        $query = 'SELECT * FROM user WHERE (username = :username OR nip = :username OR nim = :username) AND password = :password';
        $statement = $this->connection->prepare($query);
    
        // Bind params
        $statement->bindValue(':username', $username);
        $statement->bindValue(':password', $password);
        $statement->execute();
    
        // If no data found
        if ($statement->rowCount() === 0) {
            echo json_encode(['message' => 'Invalid username or password']);
            exit();
        }
    
        // If successful, generate token with expiration time and send response
        $result = $statement->fetch(\PDO::FETCH_OBJ);
        $data = [
            'username' => $result->username,
            'role' => $result->role,
            'id' => $result->id,
            'nip' => $result->nip,
            'nim' => $result->nim,
            'exp' => time() + (10 * 60) 
        ];
        $token = JWT::encode($data, AppJwt::JWT_SECRET, 'HS256');
        echo json_encode(['token' => $token]);
    }


    /**
     * Melihat info user yang mengakses berdasarkan JWT
     * @return void
     */
    public function get()
    {
        header('Content-Type: application/json');

        $allHeaders = getallheaders();
        if (!isset($allHeaders['Authorization'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            exit();
        }

        list(, $token) = explode(' ', $allHeaders['Authorization']);

        try {
            $decoded = JWT::decode($token, new Key(AppJwt::JWT_SECRET, 'HS256'));
            $user = [
                'id' => $decoded->id,
                'role' => $decoded->role,
                'username' => $decoded->username,
            ];

            if ($decoded->role === 'dosen') {
                // Query dosen data
                $queryDosen = 'SELECT * FROM dosen WHERE NIP = :nip';
                $statementDosen = $this->connection->prepare($queryDosen);
                $statementDosen->bindValue(':nip', $decoded->nip);
                $statementDosen->execute();
                $dosen = $statementDosen->fetch(\PDO::FETCH_ASSOC);

                // Add dosen-specific data
                $user['nip'] = $decoded->nip;
                $user['nama'] = $dosen['Nama'];
            } else {
                // Query mahasiswa data or other roles
                // Adjust as per your application logic
                $queryMahasiswa = 'SELECT * FROM mahasiswa WHERE NIM = :nim';
                $statementMahasiswa = $this->connection->prepare($queryMahasiswa);
                $statementMahasiswa->bindValue(':nim', $decoded->nim);
                $statementMahasiswa->execute();
                $mahasiswa = $statementMahasiswa->fetch(\PDO::FETCH_ASSOC);

                // Add mahasiswa-specific data or other roles
                $user['nim'] = $decoded->nim;
                $user['nama'] = $mahasiswa['Nama'];
                $user['semester'] = $mahasiswa['Semester'];
            }

            echo json_encode($user);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
        }
    }
}

?>