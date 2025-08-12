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
    $email = $data['email'] ?? '';
    $mobile = $data['mobile'] ?? '';
    $password = $data['password'] ?? '';
    $email_alerts = isset($data['email_alerts']) ? (int)$data['email_alerts'] : 1;
    if (!$email || !$mobile) {
        echo json_encode(["success" => false, "error" => "Email and mobile required"]);
        exit();
    }
    $sql = "UPDATE users SET email=?, mobile=?, email_alerts=?";
    $params = [$email, $mobile, $email_alerts];
    $types = 'ssi';
    if ($password) {
        $sql .= ", password=?";
        $params[] = password_hash($password, PASSWORD_BCRYPT);
        $types .= 's';
    }
    $sql .= " WHERE id=?";
    $params[] = $user_id;
    $types .= 'i';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Update failed"]);
    }
} else {
    $sql = "SELECT email, mobile, email_alerts FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode(["success" => true, "email" => $row['email'], "mobile" => $row['mobile'], "email_alerts" => $row['email_alerts']]);
}
$conn->close();
