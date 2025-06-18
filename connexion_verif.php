<?php
session_start();
require_once 'database.php';

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    header("Location: connexion.php?error=Session expirée. Veuillez réessayer.");
    exit();
}

// Validate form data
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: connexion.php?error=Veuillez remplir tous les champs.");
    exit();
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// Basic validation
if (empty($username) || empty($password)) {
    header("Location: connexion.php?error=Veuillez remplir tous les champs.");
    exit();
}

try {
    $pdo = getPDO();
    
    // Use prepared statement to prevent SQL injection
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE pseudonyme = :username OR email = :email");
    $stmt->execute([
        ':username' => $username,
        ':email' => $username // Allow login with email too
    ]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['mot_de_passe'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['pseudonyme'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['logged_in'] = true;
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Log successful login
        $log_stmt = $pdo->prepare("INSERT INTO login_logs (user_id, status, ip_address) VALUES (:user_id, 'success', :ip)");
        $log_stmt->execute([
            ':user_id' => $user['id'],
            ':ip' => $_SERVER['REMOTE_ADDR']
        ]);
        
        // Redirect to dashboard with user's first name
        header("Location: index.html?prenom=" . urlencode($user['prenom']));
        exit();
    } else {
        // Log failed login attempt
        if ($user) {
            $log_stmt = $pdo->prepare("INSERT INTO login_logs (user_id, status, ip_address) VALUES (:user_id, 'failed', :ip)");
            $log_stmt->execute([
                ':user_id' => $user['id'],
                ':ip' => $_SERVER['REMOTE_ADDR']
            ]);
        }
        
        header("Location: connexion.php?error=Identifiants incorrects.");
        exit();
    }
} catch (PDOException $e) {
    // Log error but don't expose details to user
    error_log("Login error: " . $e->getMessage());
    header("Location: connexion.php?error=Une erreur est survenue. Veuillez réessayer plus tard.");
    exit();
}
?>
