<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'database.php';

try {
    $pdo = getPDO();
    
    // Get distance measurements from the last 24 hours
    $sql = "SELECT valeur_mesure as distance, date_mesure as date 
            FROM mesures 
            WHERE id_objet = 4 
            AND date_mesure >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND valeur_mesure IS NOT NULL
            ORDER BY date_mesure DESC 
            LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert to proper format
    $data = array_map(function($row) {
        return [
            'distance' => (float)$row['distance'],
            'date' => $row['date']
        ];
    }, $results);
    
    echo json_encode(array_reverse($data)); // Reverse to show chronological order
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
