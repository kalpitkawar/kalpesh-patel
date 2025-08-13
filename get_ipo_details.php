<?php
header('Content-Type: application/json');
require_once 'config.php';

$conn = get_db_connection();
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit();
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(["error" => "Invalid ID"]);
    exit();
}
$sql = "SELECT * FROM ipos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "IPO not found"]);
}
$conn->close();
?>
