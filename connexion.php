<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="Vue/connexion.css">
</head>

<body>
    <form action="connexion_verif.php" method="POST">
        <div class="form-container">
            <h1><strong>Se connecter</strong></h1>

            <!-- Error Message Display -->
            <?php
             if (isset($_GET['error'])): ?>
                <div class="form-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; 
            ?>
            

            <!-- Form Group for Username -->
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required placeholder="Entrez votre nom d'utilisateur">
            </div>

            <!-- Form Group for Password -->
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required placeholder="Entrez votre mot de passe">
            </div>

            <!-- Forgot Password Link -->
            <div class="forgot-password">
                <a href="mdp_oublie/mdp1.php">Mot de passe oublié ?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">Se connecter</button>
        </div>
    </form>
</body>

</html>
