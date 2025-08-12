<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ipo_pulse";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}
$sql = "SELECT * FROM ipos ORDER BY open_date DESC";
$result = $conn->query($sql);
$ipos = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $ipos[] = $row;
    }
}
echo json_encode($ipos);
$conn->close();
