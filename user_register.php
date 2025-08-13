<?php
header('Content-Type: application/json');
require_once 'config.php';

$conn = get_db_connection();
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit();
}
$data = json_decode(file_get_contents('php://input'), true);
$user = $data['username'] ?? '';
$email = $data['email'] ?? '';
$mobile = $data['mobile'] ?? '';
$pass = $data['password'] ?? '';
if (!$user || !$email || !$mobile || !$pass) {
    echo json_encode(["success" => false, "error" => "All fields required"]);
    exit();
}
$hash = password_hash($pass, PASSWORD_BCRYPT);
$sql = "INSERT INTO users (username, email, mobile, password) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssss', $user, $email, $mobile, $hash);
if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Username, email, or mobile already exists"]);
}
$conn->close();
