<?php
header('Content-Type: application/json');
require_once 'config.php';

$type = isset($_GET['type']) && $_GET['type'] === 'closed' ? 'closed' : 'upcoming';

// API Configuration
$api_keys = [
    '275bc80c68msh2775dc79640d6b8p132d1bjsn5638e27e1e06',
    // Add backup API keys here if available
];

$url = $type === 'closed'
    ? 'https://indian-ipos1.p.rapidapi.com/closed-ipos'
    : 'https://indian-ipos1.p.rapidapi.com/upcoming-ipos';

function fetch_with_fallback($url, $api_keys, $type) {
    $response = null;
    $last_error = '';
    
    foreach ($api_keys as $api_key) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'x-rapidapi-host: indian-ipos1.p.rapidapi.com',
                'x-rapidapi-key: ' . $api_key
            ]
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        // Log the API call
        log_api_call($url, $http_code, $response ? substr($response, 0, 1000) : null, $error);
        
        if (!$error && $http_code === 200) {
            $decoded = json_decode($response, true);
            if (is_array($decoded) && !empty($decoded)) {
                return ['success' => true, 'data' => $decoded];
            }
        }
        
        $last_error = $error ?: "HTTP $http_code";
    }
    
    return ['success' => false, 'error' => $last_error];
}

function get_fallback_data($type) {
    // Return sample/cached data as fallback
    if ($type === 'upcoming') {
        return [
            [
                'name' => 'Sample Upcoming IPO 1',
                'openingDate' => '2025-02-01',
                'closingDate' => '2025-02-05',
                'priceBand' => '100-120',
                'description' => 'Sample upcoming IPO data (API unavailable)',
                'ipoStatus' => 'upcoming'
            ],
            [
                'name' => 'Sample Upcoming IPO 2',
                'openingDate' => '2025-02-10',
                'closingDate' => '2025-02-14',
                'priceBand' => '80-100',
                'description' => 'Sample upcoming IPO data (API unavailable)',
                'ipoStatus' => 'upcoming'
            ]
        ];
    } else {
        return [
            [
                'name' => 'Sample Closed IPO 1',
                'openingDate' => '2025-01-10',
                'closingDate' => '2025-01-14',
                'priceBand' => '150-180',
                'description' => 'Sample closed IPO data (API unavailable)',
                'ipoStatus' => 'closed'
            ]
        ];
    }
}

// Try to fetch from API
$result = fetch_with_fallback($url, $api_keys, $type);

if ($result['success']) {
    echo json_encode($result['data']);
} else {
    // Log the fallback usage
    error_log("API fetch failed for $type IPOs, using fallback data: " . $result['error']);
    
    // Return fallback data with a flag indicating it's not live
    $fallback_data = get_fallback_data($type);
    echo json_encode([
        'data' => $fallback_data,
        'fallback' => true,
        'message' => 'Live API data unavailable, showing sample data'
    ]);
}
?>