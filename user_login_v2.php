<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

$conn = get_db_connection();
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit();
}
$data = json_decode(file_get_contents('php://input'), true);
$login = $data['login'] ?? '';
$pass = $data['password'] ?? '';
$sql = "SELECT * FROM users WHERE username = ? OR email = ? OR mobile = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $login, $login, $login);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    if (password_verify($pass, $row['password'])) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid credentials"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid credentials"]);
}
$conn->close();
