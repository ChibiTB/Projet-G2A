<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'database.php';

try {
    $pdo = getPDO();
    
    // Validate and sanitize inputs
    $start = $_GET['start'] ?? date('Y-m-d', strtotime('-6 days'));
    $end = $_GET['end'] ?? date('Y-m-d');
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
        throw new Exception('Format de date invalide');
    }
    
    // Validate date range
    if (strtotime($start) > strtotime($end)) {
        throw new Exception('La date de début doit être antérieure à la date de fin');
    }
    
    // Validate capteurs parameter
    $capteurs = [];
    if (isset($_GET['capteurs']) && $_GET['capteurs'] !== '') {
        $capteurs = array_map('intval', explode(',', $_GET['capteurs']));
        $capteurs = array_filter($capteurs, function($id) {
            return $id >= 1 && $id <= 5; // Valid sensor IDs
        });
    }
    
    if (empty($capteurs)) {
        $capteurs = [1, 2, 3, 4, 5]; // Default to all sensors
    }

    // Generate all dates in range
    $allDates = [];
    $period = new DatePeriod(
        new DateTime($start),
        new DateInterval('P1D'),
        (new DateTime($end))->modify('+1 day')
    );
    foreach ($period as $date) {
        $allDates[] = $date->format('Y-m-d');
    }

    // Build query
    $placeholders = implode(',', array_fill(0, count($capteurs), '?'));
    $params = array_merge(["$start 00:00:00", "$end 23:59:59"], $capteurs);
    
    $sql = "SELECT id_objet, DATE(date_mesure) AS jour,
                    MAX(valeur_mesure) AS max_valeur,
                    MIN(valeur_mesure) AS min_valeur,
                    AVG(valeur_mesure) AS avg_valeur,
                    COUNT(*) as nb_mesures
            FROM mesures
            WHERE date_mesure BETWEEN ? AND ? 
            AND id_objet IN ($placeholders)
            AND valeur_mesure IS NOT NULL
            GROUP BY id_objet, jour
            ORDER BY jour, id_objet";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sensor names mapping
    $names = [
        1 => 'Température',
        2 => 'Humidité',
        3 => 'Luminosité',
        4 => 'Distance',
        5 => 'Sonore'
    ];

    // Organize data as [sensor][date] => info
    $matrix = [];
    foreach ($rows as $row) {
        $jour = $row['jour'];
        $capteur = (int)$row['id_objet'];
        $matrix[$capteur][$jour] = [
            'max' => round((float)$row['max_valeur'], 2),
            'min' => round((float)$row['min_valeur'], 2),
            'moyenne' => round((float)$row['avg_valeur'], 2),
            'nb_mesures' => (int)$row['nb_mesures']
        ];
    }

    // Build final data array
    $data = [];
    foreach ($capteurs as $capteur) {
        $sensorName = $names[$capteur] ?? "Capteur $capteur";
        foreach ($allDates as $jour) {
            $entry = $matrix[$capteur][$jour] ?? null;
            $data[] = [
                'jour' => $jour,
                'capteur' => $sensorName,
                'max' => $entry ? $entry['max'] : null,
                'min' => $entry ? $entry['min'] : null,
                'moyenne' => $entry ? $entry['moyenne'] : null,
                'nb_mesures' => $entry ? $entry['nb_mesures'] : 0
            ];
        }
    }

    // Add metadata
    $response = [
        'data' => $data,
        'metadata' => [
            'start_date' => $start,
            'end_date' => $end,
            'sensors' => array_map(function($id) use ($names) {
                return ['id' => $id, 'name' => $names[$id]];
            }, $capteurs),
            'total_records' => count($data),
            'date_range_days' => count($allDates)
        ]
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur de base de données',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Erreur de validation',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
