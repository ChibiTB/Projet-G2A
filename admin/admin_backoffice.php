<?php
session_start();
require 'controllers/ContentEditorController.php';
require 'models/ContentEditorModel.php';
require 'database';

$model = new ContentEditorModel($conn);
$controller = new ContentEditorController($model);
$controller->checkAuth();

$message = $controller->handleFormSubmission($_POST);
$pages = $controller->getAllPages();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Backoffice - Politique & Légal</title>
    <link rel="stylesheet" href="css/editor.css">
</head>
<body>
    <div class="content-editor">
        <h1>Gestion des contenus légaux</h1>
        <?php if ($message): ?>
            <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST">
            <?php foreach ($pages as $id => $page): ?>
                <div class="editor-section">
                    <h2><?= htmlspecialchars($page['name']) ?></h2>
                    <textarea name="<?= $id ?>" rows="10" style="width:100%;"><?= htmlspecialchars($page['content']) ?></textarea>
                </div>
            <?php endforeach; ?>
            <div class="button-group">
                <button class="save-btn" type="submit">Enregistrer</button>
                <a href="accueil.php" class="back-btn">Retour</a>
            </div>
        </form>
    </div>
</body>
</html>