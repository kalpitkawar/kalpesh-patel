<?php
// Run this script to sync live API IPOs into your database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ipo_pulse";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("DB connection failed");
}
function fetch_api($type) {
    $url = $type === 'closed'
        ? 'https://indian-ipos1.p.rapidapi.com/closed-ipos'
        : 'https://indian-ipos1.p.rapidapi.com/upcoming-ipos';
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            'x-rapidapi-host: indian-ipos1.p.rapidapi.com',
            'x-rapidapi-key: 275bc80c68msh2775dc79640d6b8p132d1bjsn5638e27e1e06'
        ]
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}
$types = ['upcoming', 'closed'];
foreach ($types as $type) {
    $api_ipos = fetch_api($type);
    if (!is_array($api_ipos)) continue;
    foreach ($api_ipos as $ipo) {
        $name = $conn->real_escape_string($ipo['name'] ?? '');
        $open = $conn->real_escape_string($ipo['openingDate'] ?? '');
        $close = $conn->real_escape_string($ipo['closingDate'] ?? '');
        $price = $conn->real_escape_string($ipo['priceBand'] ?? '');
        $details = $conn->real_escape_string($ipo['description'] ?? '');
        $status = $type;
        if (!$name || !$open || !$close) continue;
        // Upsert by name+open+close
        $sql = "INSERT INTO ipos (name, open_date, close_date, price, details, status) VALUES ('$name', '$open', '$close', '$price', '$details', '$status') ON DUPLICATE KEY UPDATE price='$price', details='$details', status='$status'";
        $conn->query($sql);
    }
}
$conn->close();
echo "Sync complete.";
