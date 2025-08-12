<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ipo_pulse";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}
$sql = "SELECT id, title, content, published_at FROM news ORDER BY published_at DESC";
$result = $conn->query($sql);
$news = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $news[] = $row;
    }
}
echo json_encode($news);
$conn->close();
?>
