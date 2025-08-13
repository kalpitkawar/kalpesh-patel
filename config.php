<?php
// Database configuration for IPO Pulse
// Check if we're in local development environment
if (file_exists(__DIR__ . '/config_local.php') && 
    (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] !== 'ipopulse.host')) {
    require_once __DIR__ . '/config_local.php';
} else {
    // Production configuration for Hostinger
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

    function log_api_call($endpoint, $status, $data, $error = null) {
        // Simple error logging for production
        error_log("API Call: $endpoint, Status: $status, Error: $error");
    }
}
?>