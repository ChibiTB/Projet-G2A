<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $host = '144.76.54.100';
    $dbname = 'G2';
    $user = 'G2';
    $pass = 'APPG2-BDD';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupération des champs
        $pseudonyme = htmlspecialchars($_POST['nom_utilisateur']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $nom = htmlspecialchars($_POST['nom']);
        $email = htmlspecialchars($_POST['email']);
        $mot_de_passe = $_POST['mot_de_passe'];

        // Vérification de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = "Adresse email invalide.";
        } else {
            $mot_de_passe_hashed = password_hash($mot_de_passe, PASSWORD_DEFAULT);

            // Insertion SQL
            $stmt = $pdo->prepare("INSERT INTO utilisateur (pseudonyme, prenom, nom, email, mot_de_passe) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$pseudonyme, $prenom, $nom, $email, $mot_de_passe_hashed]);

            // Redirection vers index.html avec prénom
            header("Location: index.html?prenom=" . urlencode($prenom));
            exit;
        }

    } catch (PDOException $e) {
        $erreur = "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'inscription</title>
    <script src="https://www.google.com/recaptcha/enterprise.js?render=6Lcagq8qAAAAAHKzx912oZfWqfnI-JOOSKyOFcdQ"></script>
    <link rel="stylesheet" href="Vue/inscription.css">
</head>
<body>
    <?php if (isset($erreur)): ?>
        <div class="error"><?php echo $erreur; ?></div>
    <?php endif; ?>

    <form id="demo-form" action="inscription.php" method="post">
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
                <p><em>Veuillez prouver que vous n'êtes pas un robot en remplissant le reCAPTCHA ci-dessous.</em></p>
            </div>

            <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
            <button type="submit" class="g-recaptcha" data-sitekey="6Lcagq8qAAAAAHKzx912oZfWqfnI-JOOSKyOFcdQ" data-callback='onSubmit' data-action='submit'>
                S'inscrire
            </button>
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

        grecaptcha.ready(function() {
            grecaptcha.execute('6Lcagq8qAAAAAHKzx912oZfWqfnI-JOOSKyOFcdQ', { action: 'submit' }).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });

        function onSubmit(token) {
            document.getElementById("demo-form").submit();
        }
    </script>
</body>
</html>