<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de Passe Oublié</title>
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
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <h1>Mot de Passe Oublié</h1>
        <p>Veuillez entrer votre adresse e-mail pour recevoir un lien de réinitialisation.</p>
        <?php
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

        require '../vendor/autoload.php';


        $host = '144.76.54.100';
        $dbname = 'G2';
        $user = 'G2';
        $pass = 'APPG2-BDD';

        $conn = new mysqli($host, $user, $pass, $dbname);
        if ($conn->connect_error) {
            die("Échec de la connexion : " . $conn->connect_error);
        }

        $message = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
            $email = $conn->real_escape_string($_POST['email']);
            $sql = "SELECT * FROM Utilisateur WHERE email='$email'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $token = bin2hex(random_bytes(50));
                $expire_time = date("Y-m-d H:i:s", strtotime('+24 hour'));
                $sql = "UPDATE Utilisateur SET token='$token', token_expiration='$expire_time' WHERE email='$email'";
                $conn->query($sql);

                // Vérifie le port MAMP
                $port = "8888"; // Change si nécessaire
                $reset_link = "http://localhost:$port/Projet-G2A/mdp_oublie/reinitialisation_mdp.php?token=$token";

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'dbreeze.g2aisep@gmail.com';
                    $mail->Password   = 'Dbreeze1234';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('dbreeze.g2aisep@gmail.com', 'DBreeze Support');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Réinitialisation de votre mot de passe';
                    $mail->Body    = "Cliquez sur ce lien pour réinitialiser votre mot de passe : <a href='$reset_link'>$reset_link</a>";

                    $mail->send();
                    $message = "Lien de réinitialisation envoyé.";
                } catch (Exception $e) {
                    $message = "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}";
                }
            } else {
                $message = "Adresse mail incorrecte.";
            }
        }

        $conn->close();
        ?>
        <form method="POST">
            <input type="email" id="email" name="email" placeholder="Votre adresse e-mail" required>
            <button type="submit">Entrer</button>
        </form>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
    </div>
</body>
</html>
