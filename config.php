<?php
// Database configuration for IPO Pulse
$DB_HOST = 'localhost';
$DB_USER = 'u159902515_u159902515_E5D'; // full username from Hostinger
$DB_PASS = '*G058=7a8';
$DB_NAME = 'u159902515_IPOPULSE'; // exact database name from Hostinger

function get_db_connection() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }
    return $conn;
}
?>