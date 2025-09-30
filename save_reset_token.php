<?php
// save_reset_token.php - Improved version
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include koneksi database
include 'conf/koneksi.php';

// Error logging function
function logError($message, $data = null) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    if ($data) {
        $logMessage .= " - Data: " . json_encode($data);
    }
    error_log($logMessage);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Ambil data JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email']) || !isset($input['token'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and token required']);
    exit();
}

$email = trim($input['email']);
$token = trim($input['token']);

// Validasi email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

try {
    // Cek koneksi database
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Buat tabel password_reset_tokens jika belum ada
    $createTable = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        used BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_token (token),
        INDEX idx_expires_at (expires_at)
    )";
    
    if (!$conn->query($createTable)) {
        logError('Failed to create table', ['error' => $conn->error]);
        throw new Exception('Failed to create table: ' . $conn->error);
    }

    // Cek apakah email ada di database users
    $userFound = false;
    $userTables = [
        ['table' => 'penyewa', 'email_columns' => ['email_penyewa', 'email']],
        ['table' => 'users', 'email_columns' => ['email', 'email_penyewa']],
        ['table' => 'tb_penyewa', 'email_columns' => ['email_penyewa', 'email']],
        ['table' => 'tb_users', 'email_columns' => ['email', 'email_penyewa']]
    ];

    foreach ($userTables as $tableInfo) {
        $tableName = $tableInfo['table'];
        
        // Cek apakah tabel ada
        $tableExists = $conn->query("SHOW TABLES LIKE '$tableName'");
        if ($tableExists->num_rows == 0) {
            continue; // Tabel tidak ada, lanjut ke tabel berikutnya
        }

        foreach ($tableInfo['email_columns'] as $emailColumn) {
            try {
                // Cek apakah kolom ada
                $columnExists = $conn->query("SHOW COLUMNS FROM $tableName LIKE '$emailColumn'");
                if ($columnExists->num_rows == 0) {
                    continue; // Kolom tidak ada, lanjut ke kolom berikutnya
                }

                $checkUserStmt = $conn->prepare("SELECT $emailColumn FROM $tableName WHERE $emailColumn = ? LIMIT 1");
                if ($checkUserStmt) {
                    $checkUserStmt->bind_param("s", $email);
                    $checkUserStmt->execute();
                    $userResult = $checkUserStmt->get_result();
                    
                    if ($userResult->num_rows > 0) {
                        $userFound = true;
                        logError('User found in table', ['table' => $tableName, 'column' => $emailColumn, 'email' => $email]);
                        break 2; // Break dari kedua loop
                    }
                    $checkUserStmt->close();
                }
            } catch (Exception $e) {
                logError('Error checking user in table', ['table' => $tableName, 'column' => $emailColumn, 'error' => $e->getMessage()]);
                continue;
            }
        }
    }

    if (!$userFound) {
        logError('User not found in any table', ['email' => $email]);
        http_response_code(404);
        echo json_encode([
            'success' => false, 
            'message' => 'Email not found in database',
            'debug_info' => [
                'email' => $email,
                'checked_tables' => array_column($userTables, 'table')
            ]
        ]);
        exit();
    }

    // Hapus token lama untuk email ini
    $deleteOldStmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE email = ?");
    if (!$deleteOldStmt) {
        throw new Exception('Failed to prepare delete query: ' . $conn->error);
    }
    $deleteOldStmt->bind_param("s", $email);
    $deleteOldStmt->execute();
    $deleteOldStmt->close();
    
    logError('Old tokens deleted', ['email' => $email, 'affected_rows' => $conn->affected_rows]);

    // Simpan token baru (berlaku 24 jam)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $insertTokenStmt = $conn->prepare("INSERT INTO password_reset_tokens (email, token, expires_at, used) VALUES (?, ?, ?, FALSE)");
    
    if (!$insertTokenStmt) {
        throw new Exception('Failed to prepare insert query: ' . $conn->error);
    }
    
    $insertTokenStmt->bind_param("sss", $email, $token, $expiresAt);
    
    if ($insertTokenStmt->execute()) {
        $insertTokenStmt->close();
        
        logError('Token saved successfully', [
            'email' => $email,
            'token_length' => strlen($token),
            'expires_at' => $expiresAt
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Token saved successfully',
            'expires_at' => $expiresAt,
            'debug_info' => [
                'email' => $email,
                'token_length' => strlen($token),
                'expires_at' => $expiresAt,
                'user_found_in_db' => true
            ]
        ]);
    } else {
        throw new Exception('Failed to execute insert query: ' . $insertTokenStmt->error);
    }
    
} catch (Exception $e) {
    logError('Save token error', [
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'email' => $email ?? 'unknown'
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred',
        'debug_info' => [
            'error_message' => $e->getMessage(),
            'line' => $e->getLine(),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>