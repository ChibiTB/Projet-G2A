_<?php
session_start(); // Toujours en tout début

// Connexion à ta vraie base de données
$host = '144.76.54.100';
$dbname = 'G2';
$user = 'G2';
$pass = 'APPG2-BDD';

// Connexion MySQLi
$conn = new mysqli($host, $user, $pass, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$message = '';

// Vérifie si le formulaire est rempli
if (isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sécurisation avec requête préparée
    $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE pseudonyme = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si l'utilisateur existe
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Vérifie le mot de passe haché
        if (password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_pseudonyme'] = $user['pseudonyme'];

            // Redirige vers index.html avec prénom dans l'URL
            header("Location: index.html?prenom=" . urlencode($user['prenom']));
            exit;
        } else {
            $message = "Mot de passe ou nom incorrect.";
        }
    } else {
        $message = "Mot de passe ou nom incorrect.";
    }

    $stmt->close();
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = "Veuillez remplir tous les champs.";
}

// Déconnexion (facultatif si tu veux le gérer)
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();

    // Supprime le cookie session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]);
    }

    header("Location: ../connexion.php?logout=success");
    exit;
}

// Si erreur, renvoyer vers page de connexion
if ($message) {
    header("Location: ../connexion.php?error=" . urlencode($message));
    exit;
}
?>