<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';
$conn = get_db_connection();
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit();
}
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_id']) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit();
}
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $alerts = isset($data['email_alerts']) ? (int)$data['email_alerts'] : 1;
    $sql = "UPDATE users SET email_alerts=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $alerts, $user_id);
    $stmt->execute();
    echo json_encode(["success" => true, "email_alerts" => $alerts]);
} else {
    $sql = "SELECT email_alerts FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode(["success" => true, "email_alerts" => $row['email_alerts']]);
}
$conn->close();
