<?php
/**
 * Simple health check endpoint for monitoring
 * Access via: /health_check.php
 */

header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

// Check database connection
try {
    require_once 'config.php';
    $conn = get_db_connection();
    
    if ($conn && !$conn->connect_error) {
        $health['checks']['database'] = [
            'status' => 'ok',
            'message' => 'Database connection successful'
        ];
    } else {
        $health['checks']['database'] = [
            'status' => 'error',
            'message' => 'Database connection failed'
        ];
        $health['status'] = 'unhealthy';
    }
    
    if ($conn) $conn->close();
} catch (Exception $e) {
    $health['checks']['database'] = [
        'status' => 'error', 
        'message' => 'Database error: ' . $e->getMessage()
    ];
    $health['status'] = 'unhealthy';
}

// Check API endpoints
$api_checks = [
    'get_ipos' => '/get_ipos.php',
    'get_news' => '/get_news.php'
];

foreach ($api_checks as $name => $endpoint) {
    $url = 'http://' . $_SERVER['HTTP_HOST'] . $endpoint;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $health['checks'][$name] = [
            'status' => 'ok',
            'response_code' => $http_code
        ];
    } else {
        $health['checks'][$name] = [
            'status' => 'error',
            'response_code' => $http_code ?: 'timeout'
        ];
        $health['status'] = 'unhealthy';
    }
}

// Check file permissions
$critical_files = ['config.php', 'get_ipos.php', 'admin_login.php'];
$permission_issues = [];

foreach ($critical_files as $file) {
    if (!file_exists($file)) {
        $permission_issues[] = "$file missing";
    } elseif (!is_readable($file)) {
        $permission_issues[] = "$file not readable";
    }
}

if (empty($permission_issues)) {
    $health['checks']['file_permissions'] = [
        'status' => 'ok',
        'message' => 'All critical files accessible'
    ];
} else {
    $health['checks']['file_permissions'] = [
        'status' => 'error',
        'issues' => $permission_issues
    ];
    $health['status'] = 'unhealthy';
}

// Check disk space (if function exists)
if (function_exists('disk_free_bytes')) {
    $free_bytes = disk_free_bytes('.');
    $total_bytes = disk_total_space('.');
    $used_percent = round((($total_bytes - $free_bytes) / $total_bytes) * 100, 2);
    
    $health['checks']['disk_space'] = [
        'status' => $used_percent > 90 ? 'warning' : 'ok',
        'used_percent' => $used_percent,
        'free_mb' => round($free_bytes / 1024 / 1024, 2)
    ];
}

// Performance metrics
$health['performance'] = [
    'memory_usage_mb' => round(memory_get_usage() / 1024 / 1024, 2),
    'memory_peak_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
    'execution_time_ms' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2)
];

// Set appropriate HTTP status code
if ($health['status'] === 'unhealthy') {
    http_response_code(500);
} elseif (isset($health['checks']['disk_space']) && $health['checks']['disk_space']['status'] === 'warning') {
    http_response_code(200); // Still operational but with warnings
}

echo json_encode($health, JSON_PRETTY_PRINT);
?>