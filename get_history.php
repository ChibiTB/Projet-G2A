<?php
header('Content-Type: application/json');
require_once 'database.php';
$pdo = getPDO();

$start = $_GET['start'] ?? date('Y-m-d', strtotime('-6 days'));
$end = $_GET['end'] ?? date('Y-m-d');
$capteurs = isset($_GET['capteurs']) && $_GET['capteurs'] !== ''
    ? array_map('intval', explode(',', $_GET['capteurs']))
    : [];

$allDates = [];
$period = new DatePeriod(
    new DateTime($start),
    new DateInterval('P1D'),
    (new DateTime($end))->modify('+1 day')
);
foreach ($period as $date) {
    $allDates[] = $date->format('Y-m-d');
}

$where = 'date_mesure BETWEEN ? AND ?';
$params = ["$start 00:00:00", "$end 23:59:59"];
if (!empty($capteurs)) {
    $placeholders = implode(',', array_fill(0, count($capteurs), '?'));
    $where .= " AND id_objet IN ($placeholders)";
    $params = array_merge($params, $capteurs);
}

try {
    $sql = "SELECT id_objet, DATE(date_mesure) AS jour,
                    MAX(valeur_mesure) AS max_valeur,
                    MIN(valeur_mesure) AS min_valeur,
                    AVG(valeur_mesure) AS avg_valeur
            FROM mesures
            WHERE $where
            GROUP BY id_objet, jour
            ORDER BY jour, id_objet";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $names = [
        1 => 'Température',
        2 => 'Humidité',
        3 => 'Luminosité',
        4 => 'Distance',
        5 => 'Sonore'
    ];

    // Organise les données sous forme [capteur][date] => info
    $matrix = [];
    foreach ($rows as $row) {
        $jour = $row['jour'];
        $capteur = $row['id_objet'];
        $matrix[$capteur][$jour] = [
            'max' => (float) $row['max_valeur'],
            'min' => (float) $row['min_valeur'],
            'moyenne' => round((float) $row['avg_valeur'], 2)
        ];
    }

    $data = [];
    foreach ($capteurs as $capteur) {
        foreach ($allDates as $jour) {
            $entry = $matrix[$capteur][$jour] ?? null;
            $data[] = [
                'jour' => $jour,
                'capteur' => $names[$capteur] ?? $capteur,
                'max' => $entry['max'] ?? null,
                'min' => $entry['min'] ?? null,
                'moyenne' => $entry['moyenne'] ?? null
            ];
        }
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
