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
    $ipo_id = $data['ipo_id'] ?? 0;
    $demat_account_id = $data['demat_account_id'] ?? 0;
    $applied_lots = $data['applied_lots'] ?? 0;
    $application_date = $data['application_date'] ?? date('Y-m-d');
    if (!$ipo_id || !$demat_account_id || !$applied_lots) {
        echo json_encode(["success" => false, "error" => "All fields required"]);
        exit();
    }
    $stmt = $conn->prepare("INSERT INTO ipo_applications (user_id, ipo_id, demat_account_id, applied_lots, application_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iiiis', $user_id, $ipo_id, $demat_account_id, $applied_lots, $application_date);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "id" => $conn->insert_id]);
    } else {
        echo json_encode(["success" => false, "error" => "Insert failed"]);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? 0;
    if (!$id) {
        echo json_encode(["success" => false, "error" => "ID required"]);
        exit();
    }
    $stmt = $conn->prepare("DELETE FROM ipo_applications WHERE id=? AND user_id=?");
    $stmt->bind_param('ii', $id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Delete failed"]);
    }
} else {
    $sql = "SELECT a.id, a.ipo_id, i.name as ipo_name, a.demat_account_id, d.account_number, a.applied_lots, a.application_date, a.status, a.listing_gain FROM ipo_applications a JOIN ipos i ON a.ipo_id=i.id JOIN demat_accounts d ON a.demat_account_id=d.id WHERE a.user_id=? ORDER BY a.application_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $apps = [];
    while ($row = $result->fetch_assoc()) {
        $apps[] = $row;
    }
    echo json_encode(["success" => true, "applications" => $apps]);
}
$conn->close();
?>
