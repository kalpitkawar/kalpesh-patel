<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once 'config.php';
$conn = get_db_connection();
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}
$sql = "SELECT id, name, open_date, close_date, price, status FROM ipos ORDER BY open_date DESC";
$result = $conn->query($sql);
$ipos = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $ipos[] = $row;
    }
}
echo json_encode($ipos);
$conn->close();
?>
