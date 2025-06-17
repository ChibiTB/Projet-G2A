<?php
require_once __DIR__ . '/admin/database/db_conn.php';
$conn = getPDO();

// Détermine quelle page afficher
$page_key = $_GET['page'] ?? 'confidentialite';

$page_map = [
    'confidentialite' => 'Politique de confidentialité',
    'conditions' => "Conditions d'utilisation",
    'cookies' => "Politique de cookies",
    'mentions' => "Mentions légales"
];

$page_name = $page_map[$page_key] ?? 'Politique de confidentialité';

// Requête SQL
$stmt = $conn->prepare("SELECT content FROM multiple_content WHERE page_name = ?");
$stmt->execute([$page_name]);
$content = $stmt->fetchColumn() ?: "<p>Contenu non disponible.</p>";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_name) ?></title>
    <link rel="stylesheet" href="Vue/politique.css">
</head>
<body>
    <header class="header">
        <h1><?= htmlspecialchars($page_name) ?></h1>
    </header>
    <main class="content">
        <?= $content ?>
    </main>
</body>
</html>