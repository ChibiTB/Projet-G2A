<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $host = '144.76.54.100';
    $dbname = 'G2';
    $user = 'G2';
    $pass = 'APPG2-BDD';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ✅ Clé secrète reCAPTCHA V2
        $recaptchaToken = $_POST['g-recaptcha-response'] ?? '';
        $recaptchaSecret = '6Ldm-2ErAAAAAKwJjsu7z3PavQnQi8kvgWYQXn75'; // 👉 remplace par ta vraie clé secrète V2

        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaToken");
        $data = json_decode($response);

        if (!$data->success) {
            $erreur = "Échec du reCAPTCHA. Veuillez réessayer.";
        } else {
            // Données utilisateur
            $pseudonyme = htmlspecialchars($_POST['nom_utilisateur']);
            $prenom = htmlspecialchars($_POST['prenom']);
            $nom = htmlspecialchars($_POST['nom']);
            $email = htmlspecialchars($_POST['email']);
            $mot_de_passe = $_POST['mot_de_passe'];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreur = "Adresse email invalide.";
            } else {
                $mot_de_passe_hashed = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO utilisateur (pseudonyme, prenom, nom, email, mot_de_passe) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$pseudonyme, $prenom, $nom, $email, $mot_de_passe_hashed]);

                header("Location: index.html?prenom=" . urlencode($prenom));
                exit;
            }
        }

    } catch (PDOException $e) {
        // 23000 = code d’erreur MySQL pour violation d’unicité
        if ($e->getCode() == 23000) {
            $erreur = "🚫  Cet e-mail ou ce pseudo est déjà utilisé. ";
        } else {
            $erreur = "Erreur : " . $e->getMessage();
        }
    }
    
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire d'inscription</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- ✅ Script reCAPTCHA V2 (case à cocher) -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="Vue/inscription.css">
</head>
<body>


<form action="inscription.php" method="post" id="demo-form">
    <div class="form-container">
        <img src="images/LOGOdbreeze.png" alt="Logo DBreeze" class="logo">
        <h2><strong>FORMULAIRE D'INSCRIPTION</strong></h2>

        <div class="form-group">
            <label>Nom d'utilisateur</label>
            <input type="text" name="nom_utilisateur" required>
        </div>

        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" required>
        </div>

        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" required>
        </div>

        <div class="form-group">
            <label>Adresse mail</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Mot de Passe</label>
            <input type="password" id="password" name="mot_de_passe" required>
            <ul class="constraint-list" id="password-constraints">
                <li id="length-constraint"><span>✖</span> Au moins 9 caractères</li>
                <li id="letter-constraint"><span>✖</span> Contient au moins une lettre</li>
                <li id="number-constraint"><span>✖</span> Contient au moins un chiffre</li>
                <li id="special-constraint"><span>✖</span> Contient au moins un caractère spécial</li>
            </ul>
        </div>

        <div class="recaptcha-message">
            <p><em>Veuillez prouver que vous n'êtes pas un robot :</em></p>
        </div>

        <!-- ✅ reCAPTCHA v2 -->
        <div class="g-recaptcha" data-sitekey="6Ldm-2ErAAAAALB7WdJPQ5xHFK2rZXCnvO7EnJeW"></div> <!-- 🔁 Remplace par ta clé du site -->

        <br>
        <button type="submit">S'inscrire</button>
<!-- message d'erreur “joli” -->
<?php if (isset($erreur)): ?>
    <p class="form-error"><?= $erreur ?></p>
<?php endif; ?>
    </div>
</form>

<script>
    const passwordInput = document.getElementById('password');
    const lengthConstraint = document.getElementById('length-constraint');
    const letterConstraint = document.getElementById('letter-constraint');
    const numberConstraint = document.getElementById('number-constraint');
    const specialConstraint = document.getElementById('special-constraint');

    function validatePassword() {
        const password = passwordInput.value;
        updateConstraint(lengthConstraint, password.length >= 9);
        updateConstraint(letterConstraint, /[A-Za-z]/.test(password));
        updateConstraint(numberConstraint, /\d/.test(password));
        updateConstraint(specialConstraint, /[!@#$%^&*(),.?":{}|<>]/.test(password));
    }

    function updateConstraint(element, valid) {
        const icon = element.querySelector('span');
        icon.textContent = valid ? '✔' : '✖';
        element.classList.toggle('valid', valid);
        element.classList.toggle('invalid', !valid);
    }

    passwordInput.addEventListener('input', validatePassword);
</script>

</body>
</html>