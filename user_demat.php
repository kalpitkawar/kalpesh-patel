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
    $account_number = $data['account_number'] ?? '';
    $depository = $data['depository'] ?? '';
    $holder_name = $data['holder_name'] ?? '';
    if (!$account_number || !$depository) {
        echo json_encode(["success" => false, "error" => "Account number and depository required"]);
        exit();
    }
    $stmt = $conn->prepare("INSERT INTO demat_accounts (user_id, account_number, depository, holder_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $user_id, $account_number, $depository, $holder_name);
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
    $stmt = $conn->prepare("DELETE FROM demat_accounts WHERE id=? AND user_id=?");
    $stmt->bind_param('ii', $id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Delete failed"]);
    }
} else {
    $stmt = $conn->prepare("SELECT id, account_number, depository, holder_name FROM demat_accounts WHERE user_id=?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $accounts = [];
    while ($row = $result->fetch_assoc()) {
        $accounts[] = $row;
    }
    echo json_encode(["success" => true, "accounts" => $accounts]);
}
$conn->close();
?>
