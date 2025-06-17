<?php
header('Content-Type: application/json');
require_once 'database.php';
$pdo = getPDO();

$start = $_GET['start'] ?? date('Y-m-d', strtotime('-6 days'));
$end = $_GET['end'] ?? date('Y-m-d');
$capteurs = isset($_GET['capteurs']) && $_GET['capteurs'] !== ''
    ? array_map('intval', explode(',', $_GET['capteurs']))
    : [];

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
            ORDER BY jour DESC, id_objet";
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

    $data = [];
    foreach ($rows as $row) {
        $data[] = [
            'jour' => $row['jour'],
            'capteur' => $names[$row['id_objet']] ?? $row['id_objet'],
            'max' => (float)$row['max_valeur'],
            'min' => (float)$row['min_valeur'],
            'moyenne' => round((float)$row['avg_valeur'], 2)
        ];
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
