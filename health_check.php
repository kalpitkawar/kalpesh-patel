<?php
header('Content-Type: application/json');
require_once 'config.php';

$health_status = [
    'timestamp' => date('Y-m-d H:i:s'),
    'database' => 'unknown',
    'api_endpoints' => [],
    'overall_status' => 'unknown'
];

// Test database connection
try {
    $conn = get_db_connection();
    $result = $conn->query("SELECT COUNT(*) as count FROM ipos");
    if ($result) {
        $row = $result->fetch_assoc();
        $health_status['database'] = [
            'status' => 'connected',
            'ipo_count' => $row['count']
        ];
    }
    $conn->close();
} catch (Exception $e) {
    $health_status['database'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Test API endpoints
$api_types = ['upcoming', 'closed'];
foreach ($api_types as $type) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://indian-ipos1.p.rapidapi.com/{$type}-ipos");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-rapidapi-host: indian-ipos1.p.rapidapi.com',
        'x-rapidapi-key: 275bc80c68msh2775dc79640d6b8p132d1bjsn5638e27e1e06'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $health_status['api_endpoints'][$type] = [
        'status' => $error ? 'error' : ($http_code === 200 ? 'ok' : 'warning'),
        'http_code' => $http_code,
        'error' => $error ?: null,
        'response_size' => $response ? strlen($response) : 0
    ];
}

// Determine overall status
$overall = 'healthy';
if ($health_status['database']['status'] === 'error') {
    $overall = 'degraded';
}

$api_errors = 0;
foreach ($health_status['api_endpoints'] as $endpoint) {
    if ($endpoint['status'] === 'error') {
        $api_errors++;
    }
}

if ($api_errors === count($health_status['api_endpoints'])) {
    $overall = 'degraded';
} elseif ($api_errors > 0) {
    $overall = 'partial';
}

$health_status['overall_status'] = $overall;

// Return appropriate HTTP status code
switch ($overall) {
    case 'healthy':
        http_response_code(200);
        break;
    case 'partial':
        http_response_code(200);
        break;
    case 'degraded':
        http_response_code(503);
        break;
}

echo json_encode($health_status, JSON_PRETTY_PRINT);
?>