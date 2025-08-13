<?php
// Simple test to verify our API enhancements work without database
header('Content-Type: application/json');

// Test the fetch_ipo_api.php functionality
echo "Testing fetch_ipo_api.php...\n";

// Test upcoming IPOs
$upcoming_url = 'http://localhost/fetch_ipo_api.php?type=upcoming';
$closed_url = 'http://localhost/fetch_ipo_api.php?type=closed';

// Simulate what the frontend JavaScript would do
echo "Would fetch: $upcoming_url\n";
echo "Would fetch: $closed_url\n";

// Test the API directly
echo "\nTesting API response structure:\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/fetch_ipo_api.php?type=upcoming');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "CURL Error: $error\n";
} else {
    echo "API Response: $response\n";
}

echo "\nTest completed.\n";
?>