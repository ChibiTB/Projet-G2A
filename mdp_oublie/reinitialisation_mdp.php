<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Mot de Passe</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .forgot-password-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .forgot-password-container h1 {
            font-size: 1.8em;
            margin-bottom: 20px;
            color: #333;
        }

        .forgot-password-container p {
            font-size: 1em;
            margin-bottom: 20px;
            color: #555;
        }

        .forgot-password-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .forgot-password-container button {
            width: 100%;
            padding: 10px;
            font-size: 1.1em;
            color: #fff;
            background-color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .forgot-password-container button:hover {
            background-color: #555;
        }

        .message {
            margin-top: 20px;
            font-size: 1em;
            color: #d9534f;
        }

        .valid {
            color: green;
        }

        .invalid {
            color: red;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        li {
            margin-bottom: 15px;
        }

    </style>
</head>
<body>
    <div class="forgot-password-container">
        <h1>Nouveau Mot de Passe</h1>
        <?php
        require __DIR__ . '/../vendor/autoload.php';
        $host = '144.76.54.100';
        $dbname = 'G2';
        $user = 'G2';
        $pass = 'APPG2-BDD';

        $conn = new mysqli($host, $user, $pass, $dbname);
        if ($conn->connect_error) {
            die("Échec de la connexion : " . $conn->connect_error);
        }

        $message = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $token = $_GET['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm-password'] ?? '';

            if ($password === $confirmPassword) {
                $sql = "SELECT * FROM Utilisateur WHERE token='$token' AND token_expiration > NOW()";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE Utilisateur SET mot_de_passe='$hashedPassword', token=NULL, token_expiration=NULL WHERE token='$token'";
                    if ($conn->query($sql) === TRUE) {
                        $message = "Mot de passe modifié avec succès. Vous pouvez maintenant vous connecter.";
                    } else {
                        $message = "Erreur lors de la mise à jour du mot de passe.";
                    }
                } else {
                    $message = "Lien invalide ou expiré.";
                }
            } else {
                $message = "Les mots de passe ne correspondent pas.";
            }
        }

        $conn->close();
        ?>
        <form method="POST">
            <input type="password" id="password" name="password" placeholder="Nouveau mot de passe" required>
            <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirmez le mot de passe" required>
            <ul>
                <li id="length" class="invalid">✖ Au moins 9 caractères</li>
                <li id="letter" class="invalid">✖ Contient au moins une lettre</li>
                <li id="number" class="invalid">✖ Contient au moins un chiffre</li>
                <li id="special" class="invalid">✖ Contient au moins un caractère spécial</li>
                <li id="match" class="invalid">✖ Les mots de passe doivent être identiques</li>
            </ul>
            <button type="submit" id="submit-button" disabled>Confirmer</button>
        </form>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
    </div>

    <script>
        const password = document.getElementById("password");
        const confirmPassword = document.getElementById("confirm-password");
        const submitButton = document.getElementById("submit-button");

        const lengthCriteria = document.getElementById("length");
        const letterCriteria = document.getElementById("letter");
        const numberCriteria = document.getElementById("number");
        const specialCriteria = document.getElementById("special");
        const matchCriteria = document.getElementById("match");

        function validatePassword() {
            const value = password.value;
            const confirmValue = confirmPassword.value;

            const hasLength = value.length >= 9;
            const hasLetter = /[a-zA-Z]/.test(value);
            const hasNumber = /[0-9]/.test(value);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(value);
            const passwordsMatch = value === confirmValue && value !== "";

            updateCriteria(lengthCriteria, hasLength);
            updateCriteria(letterCriteria, hasLetter);
            updateCriteria(numberCriteria, hasNumber);
            updateCriteria(specialCriteria, hasSpecial);
            updateCriteria(matchCriteria, passwordsMatch);

            submitButton.disabled = !(hasLength && hasLetter && hasNumber && hasSpecial && passwordsMatch);
        }

        function updateCriteria(criteriaElement, condition) {
            if (condition) {
                criteriaElement.classList.remove("invalid");
                criteriaElement.classList.add("valid");
            } else {
                criteriaElement.classList.remove("valid");
                criteriaElement.classList.add("invalid");
            }
        }

        password.addEventListener("input", validatePassword);
        confirmPassword.addEventListener("input", validatePassword);
    </script>
</body>
</html>