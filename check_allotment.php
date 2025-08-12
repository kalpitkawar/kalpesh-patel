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
$accounts = [];
$sql = "SELECT account_number, depository, holder_name FROM demat_accounts WHERE user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $accounts[] = $row;
}
// Simulate allotment check (replace with real API if available)
$allotments = [];
foreach ($accounts as $acc) {
    $allotments[] = [
        'account_number' => $acc['account_number'],
        'depository' => $acc['depository'],
        'holder_name' => $acc['holder_name'],
        'status' => (rand(0,1) ? 'Allotted' : 'Not Allotted')
    ];
}
echo json_encode(["success" => true, "allotments" => $allotments]);
$conn->close();
?>
