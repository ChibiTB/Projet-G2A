<?php
include 'database/db_conn.php';

// Liste des pages autorisées
$pages = [
    'confidentialite' => 'Politique de confidentialité',
    'cookies' => 'Cookies',
    'mentions' => 'Mentions légales',
    'conditions' => "Conditions d'utilisation"
];

// Récupération du paramètre `page` depuis l'URL
$page_key = $_GET['page'] ?? '';
$page_name = $pages[$page_key] ?? '';

if (!$page_name) {
    http_response_code(404);
    echo "<h1>Erreur 404 - Page non trouvée</h1>";
    exit;
}

// Récupération du contenu depuis la base de données
$conn->set_charset("utf8mb4");
$sql = "SELECT content FROM multiple_content WHERE page_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $page_name);
$stmt->execute();
$stmt->bind_result($content);
$stmt->fetch();
$stmt->close();
$conn->close();

if (empty($content)) {
    $content = "<p>Contenu non disponible pour cette page.</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/mentions-legales.css">
    <link rel="stylesheet" href="css/footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
</head>
<body>

<header class="header">
    <a href="accueil.php" class="home-icon" title="Accueil"><i class="fas fa-home"></i></a>
    <h1><?= htmlspecialchars($page_name) ?></h1>
</header>

<main class="content">
    <?= $content ?>
</main>

<?php include 'navigation/footer.php'; ?>

</body>
</html>