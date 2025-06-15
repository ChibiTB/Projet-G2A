<?php
session_start();
require_once 'database.php';
require_once 'models/ContentEditorModel.php';
require_once 'controllers/ContentEditorController.php';

$model = new ContentEditorModel($conn);
$controller = new ContentEditorController($model);
$controller->checkAuth();

$message = $controller->handleFormSubmission($_POST);
$pages = $controller->getAllPages();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Éditeur de contenu</title>
    <link rel="stylesheet" href="css/editor.css"> <!-- Ton CSS -->
</head>
<body>
    <div class="content-editor">
        <h1>Modifier le contenu</h1>
        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'succès') !== false ? 'success' : 'error' ?>">
                <?= $message ?>
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