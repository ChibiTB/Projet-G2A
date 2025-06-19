<?php
function getPDO() {
    // Move these to environment variables or a secure config file
    $host = getenv('DB_HOST') ?: '144.76.54.100';
    $dbname = getenv('DB_NAME') ?: 'G2';
    $user = getenv('DB_USER') ?: 'G2';
    $pass = getenv('DB_PASS') ?: 'APPG2-BDD';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}

function getSMTPConfig() {
    // Move these to environment variables or a secure config file
    return [
        'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'username' => getenv('SMTP_USER') ?: 'dbreeze.g2aisep@gmail.com',
        'password' => getenv('SMTP_PASS') ?: 'emxz uzkh qzhn iycd',
        'port' => getenv('SMTP_PORT') ?: 587,
        'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls'
    ];
}

// Add CSRF token generation and validation
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
