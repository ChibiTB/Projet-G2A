<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connexion avec PDO
require_once __DIR__ . '/database/db_conn.php';
require_once 'controllers/AdminController.php';
require_once 'models/ContentEditorModel.php';

$pdo = getPDO();
$model = new ContentEditorModel($pdo);
$adminController = new AdminController($model);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($adminController->login($username, $password)) {
        header("Location: editor.php");
        exit();
    } else {
        $error = "Identifiants invalides.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin</title>
</head>
<body>
    <h1>Connexion Admin</h1>
    <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
</body>
</html>