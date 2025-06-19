<?php
session_start();

/* ---------- 1) Connexion MySQLi ---------- */
$host   = '144.76.54.100';
$dbname = 'G2';
$user   = 'G2';
$pass   = 'APPG2-BDD';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('Erreur de connexion : ' . $conn->connect_error);
}

/* ---------- 2) Logique de connexion ---------- */
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    /* a) Champs obligatoires */
    if ($username === '' || $password === '') {
        $message = 'Veuillez remplir tous les champs.';

    } else {
        /* b) Recherche de l’utilisateur */
        $stmt = $conn->prepare('SELECT * FROM utilisateur WHERE pseudonyme = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            /* c) Vérification du mot de passe */
            if (password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['user_logged_in']  = true;
                $_SESSION['user_id']         = $user['id'];
                $_SESSION['user_prenom']     = $user['prenom'];
                $_SESSION['user_nom']        = $user['nom'];
                $_SESSION['user_pseudonyme'] = $user['pseudonyme'];

                header('Location: index.html?prenom=' . urlencode($user['prenom']));
                exit;
            } else {
                $message = 'Mot de passe incorrect.';
            }
        } else {
            $message = "Ce compte n'existe pas encore. Veuillez vous inscrire d'abord.";
        }
        $stmt->close();
    }
}

/* ---------- 3) Déconnexion (facultatif) ---------- */
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ../connexion.php?logout=success');
    exit;
}

/* ---------- 4) Retour à la page de connexion avec message ---------- */
if ($message !== '') {
    header('Location: ../connexion.php?error=' . urlencode($message));
    exit;
}
?>