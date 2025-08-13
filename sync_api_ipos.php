<?php
// Run this script to sync live API IPOs into your database
require_once 'config.php';

$conn = get_db_connection();

function fetch_api($type) {
    $api_keys = [
        '275bc80c68msh2775dc79640d6b8p132d1bjsn5638e27e1e06',
        // Add backup API keys here if available
    ];
    
    $url = $type === 'closed'
        ? 'https://indian-ipos1.p.rapidapi.com/closed-ipos'
        : 'https://indian-ipos1.p.rapidapi.com/upcoming-ipos';
    
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
                return $decoded;
            }
        }
        
        $last_error = $error ?: "HTTP $http_code";
    }
    
    error_log("Failed to fetch $type IPOs: $last_error");
    return false;
}

$sync_results = [
    'upcoming' => ['success' => 0, 'failed' => 0],
    'closed' => ['success' => 0, 'failed' => 0]
];

$types = ['upcoming', 'closed'];
foreach ($types as $type) {
    echo "Syncing $type IPOs...\n";
    
    $api_ipos = fetch_api($type);
    if (!$api_ipos) {
        echo "Failed to fetch $type IPOs from API\n";
        continue;
    }
    
    foreach ($api_ipos as $ipo) {
        try {
            $name = $conn->real_escape_string($ipo['name'] ?? '');
            $open = $conn->real_escape_string($ipo['openingDate'] ?? '');
            $close = $conn->real_escape_string($ipo['closingDate'] ?? '');
            $price = $conn->real_escape_string($ipo['priceBand'] ?? '');
            $details = $conn->real_escape_string($ipo['description'] ?? '');
            $status = $type;
            
            if (!$name || !$open || !$close) {
                echo "Skipping IPO with missing data: " . json_encode($ipo) . "\n";
                $sync_results[$type]['failed']++;
                continue;
            }
            
            // Use prepared statement for better security
            $sql = "INSERT INTO ipos (name, open_date, close_date, price, details, status) 
                    VALUES (?, ?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    price=VALUES(price), details=VALUES(details), status=VALUES(status)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssss', $name, $open, $close, $price, $details, $status);
            
            if ($stmt->execute()) {
                $sync_results[$type]['success']++;
                echo "Synced: $name\n";
            } else {
                echo "Failed to sync: $name - " . $stmt->error . "\n";
                $sync_results[$type]['failed']++;
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            echo "Error syncing IPO: " . $e->getMessage() . "\n";
            $sync_results[$type]['failed']++;
        }
    }
}

$conn->close();

echo "\nSync Summary:\n";
foreach ($sync_results as $type => $result) {
    echo "$type: {$result['success']} synced, {$result['failed']} failed\n";
}
echo "Sync complete.\n";
?>