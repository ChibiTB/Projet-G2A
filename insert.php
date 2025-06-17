<?php
// Inclure la fonction de connexion PDO
require("database.php");

// Récupérer l'instance PDO
$pdo = getPDO(); // 🔧 C'était ça qui manquait

// Lire les données POST
$id_objet = isset($_POST['id_objet']) ? (int)$_POST['id_objet'] : 0;
$valeur_mesure = isset($_POST['valeur_mesure']) ? (float)$_POST['valeur_mesure'] : 0.0;

try {
    // Préparer la requête avec PDO
    $stmt = $pdo->prepare("INSERT INTO mesures (id_objet, valeur_mesure, date_mesure) VALUES (?, ?, NOW())");
    $stmt->execute([$id_objet, $valeur_mesure]);

    echo "Mesure insérée avec succès.";
} catch (PDOException $e) {
    echo "Erreur lors de l'insertion : " . $e->getMessage();
}
?>
