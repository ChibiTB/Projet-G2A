<?php
header('Content-Type: application/json');

require_once 'database.php';
$pdo = getPDO();

try {

    $stmt = $pdo->prepare("
        SELECT id_objet, date_mesure, valeur_mesure
        FROM mesures
        WHERE id_objet = 2
        ORDER BY date_mesure DESC
        LIMIT 50
    ");
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(array_reverse($data)); 

} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur BDD : ' . $e->getMessage()]);
}
?>
