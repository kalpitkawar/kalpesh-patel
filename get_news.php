<?php
header('Content-Type: application/json');
require_once 'config.php';

$conn = get_db_connection();
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
