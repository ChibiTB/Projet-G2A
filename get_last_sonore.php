<?php
header('Content-Type: application/json');

$host = '144.76.54.100';
$dbname = 'G2';
$user = 'G2';
$pass = 'APPG2-BDD';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT valeur_mesure FROM mesures WHERE id_objet = 5 ORDER BY date_mesure DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['sonore' => floatval($result['valeur_mesure'])]);
    } else {
        echo json_encode(['sonore' => null]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
