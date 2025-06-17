<?php
session_start();

// Connexion à la BDD
require_once __DIR__ . '/database/db_conn.php';
$conn = getPDO();

// Inclusion des classes
require_once __DIR__ . '/models/ContentEditorModel.php';
require_once __DIR__ . '/controllers/ContentEditorController.php';

// Initialisation
$model = new ContentEditorModel($conn);
$controller = new ContentEditorController($model);

// Vérification de l'authentification
$controller->checkAuth();

// Traitement du formulaire
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $controller->handleFormSubmission($_POST);
}

// Récupération du contenu
$pages = $controller->getAllPages();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Éditeur de contenu</title>
    <link rel="stylesheet" href="css/editor.css"> <!-- Ton fichier CSS -->
</head>
<body>
    <div class="content-editor">
        <h1>Modifier le contenu</h1>

        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'succès') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php foreach ($pages as $page_id => $page): ?>
                <div class="editor-section">
                    <h2><?= htmlspecialchars($page['name']) ?></h2>
                    <textarea name="<?= $page_id ?>" rows="10" style="width: 100%;"><?= htmlspecialchars($page['content']) ?></textarea>
                </div>
            <?php endforeach; ?>

            <div class="button-group">
                <button type="submit" class="save-btn">Enregistrer</button>
                <a href="admin_login.php" class="back-btn">Déconnexion</a>
            </div>
        </form>
    </div>
</body>
</html>