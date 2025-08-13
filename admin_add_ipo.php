<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$conn = get_db_connection();
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit();
}
$data = json_decode(file_get_contents('php://input'), true);
$name = $data['name'] ?? '';
$open_date = $data['open_date'] ?? '';
$close_date = $data['close_date'] ?? '';
$price = $data['price'] ?? '';
$details = $data['details'] ?? '';
$status = $data['status'] ?? 'upcoming';
$sql = "INSERT INTO ipos (name, open_date, close_date, price, details, status) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssssds', $name, $open_date, $close_date, $price, $details, $status);
if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to add IPO"]);
}
$conn->close();
