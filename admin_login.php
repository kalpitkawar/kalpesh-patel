<?php
session_start();
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ipo_pulse";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit();
}
$data = json_decode(file_get_contents('php://input'), true);
$user = $data['username'] ?? '';
$pass = $data['password'] ?? '';
$sql = "SELECT * FROM admin_users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    if (password_verify($pass, $row['password'])) {
        $_SESSION['admin_logged_in'] = true;
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid credentials"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid credentials"]);
}
$conn->close();
