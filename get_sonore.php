<?php
header('Content-Type: application/json');

$host = '144.76.54.100';
$dbname = 'G2';
$user = 'G2';
$pass = 'APPG2-BDD';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT id_objet, date_mesure, valeur_mesure FROM mesures ORDER BY date_mesure ASC LIMIT 200");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grouped = [];

    foreach ($rows as $row) {
        $date = $row['date_mesure'];
        $valeur = floatval($row['valeur_mesure']);
        $id_objet = $row['id_objet'];

        if (!isset($grouped[$date])) {
            $grouped[$date] = ['date' => $date];
        }

        if ($id_objet == 5) {
            $grouped[$date]['sonore'] = $valeur;
        }
    }

    echo json_encode(array_values($grouped), JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 