<?php
require_once 'database.php';

// Simple caching mechanism
function getCachedData($cacheKey, $ttl = 60) {
    $cacheFile = sys_get_temp_dir() . '/dbreeze_cache_' . md5($cacheKey) . '.json';
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    return null;
}

function saveCache($cacheKey, $data) {
    $cacheFile = sys_get_temp_dir() . '/dbreeze_cache_' . md5($cacheKey) . '.json';
    file_put_contents($cacheFile, json_encode($data));
}

// Get parameters with defaults
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$limit = min(max($limit, 1), 500); // Ensure limit is between 1 and 500

// Generate cache key based on parameters
$cacheKey = "measures_" . $limit;

// Try to get cached data
$cachedData = getCachedData($cacheKey, 30); // 30 seconds TTL
if ($cachedData !== null) {
    header('Content-Type: application/json');
    echo json_encode($cachedData);
    exit;
}

try {
    $pdo = getPDO();
    
    // Use prepared statement with parameter binding
    $stmt = $pdo->prepare("
        SELECT 
            date_mesure as date, 
            temperature, 
            humidite 
        FROM mesure_temp_hum 
        ORDER BY date_mesure DESC 
        LIMIT :limit
    ");
    
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Save to cache
    saveCache($cacheKey, $results);
    
    header('Content-Type: application/json');
    echo json_encode($results);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error']);
    error_log("Database error in get_measures.php: " . $e->getMessage());
}
?>
