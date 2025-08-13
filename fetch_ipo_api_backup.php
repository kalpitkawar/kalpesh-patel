<?php
header('Content-Type: application/json');

$type = isset($_GET['type']) && $_GET['type'] === 'closed' ? 'closed' : 'upcoming';

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
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo json_encode(["error" => $err]);
} else {
    echo $response;
}
