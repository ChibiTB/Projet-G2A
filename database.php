<?php
$host = '144.76.54.100';
$dbname = 'G2';
$user = 'G2';
$pass = 'APPG2-BDD';

//$host = 'localhost';
//$dbname = 'dbreeze';
//$user = 'root';
//$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
//    $pdo = new PDO("mysql:host=$host;dbname=$dbname;port=3307;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie à la base de données.";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
