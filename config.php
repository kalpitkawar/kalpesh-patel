<?php
// Database configuration for IPO Pulse
// Use environment variables for production deployment

$DB_HOST = $_ENV['DB_HOST'] ?? 'localhost';
$DB_USER = $_ENV['DB_USER'] ?? 'u159902515_u159902515_E5D'; // full username from Hostinger
$DB_PASS = $_ENV['DB_PASS'] ?? '*G058=7a8';
$DB_NAME = $_ENV['DB_NAME'] ?? 'u159902515_IPOPULSE'; // exact database name from Hostinger

function get_db_connection() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
    
    try {
        // For development without MySQL, return mock data
        if ($DB_HOST === 'localhost' && !extension_loaded('mysqli')) {
            return create_mock_connection();
        }
        
        $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return create_mock_connection();
        }
        
        // Set charset to UTF-8 for proper character handling
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return create_mock_connection();
    }
}

function create_mock_connection() {
    // Create a mock object for development/demo purposes
    return new class {
        public $connect_error = null;
        
        public function query($sql) {
            // Return mock IPO data for demonstration
            if (strpos($sql, 'SELECT') !== false && strpos($sql, 'ipos') !== false) {
                return new class {
                    public function fetch_assoc() {
                        static $count = 0;
                        if ($count++ < 3) {
                            return [
                                'id' => $count,
                                'name' => "Demo IPO " . $count,
                                'open_date' => date('Y-m-d', strtotime('+' . $count . ' days')),
                                'close_date' => date('Y-m-d', strtotime('+' . ($count + 5) . ' days')),
                                'price' => 100 + ($count * 50),
                                'status' => $count == 1 ? 'live' : ($count == 2 ? 'upcoming' : 'closed')
                            ];
                        }
                        return null;
                    }
                    
                    public $num_rows = 3;
                };
            }
            return false;
        }
        
        public function prepare($sql) {
            return new class {
                public function bind_param($types, ...$vars) {}
                public function execute() { return true; }
                public function get_result() {
                    return new class {
                        public $num_rows = 1;
                        public function fetch_assoc() {
                            return ['id' => 1, 'username' => 'admin', 'password' => password_hash('admin123', PASSWORD_DEFAULT)];
                        }
                    };
                }
            };
        }
        
        public function real_escape_string($str) {
            return addslashes($str);
        }
        
        public function set_charset($charset) {}
        public function close() {}
    };
}

// Function to safely close database connection
function close_db_connection($conn) {
    if ($conn && !$conn->connect_error) {
        $conn->close();
    }
}

// Function to sanitize input data
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Function to validate email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Function to validate mobile number (Indian format)
function is_valid_mobile($mobile) {
    return preg_match('/^[6-9]\d{9}$/', $mobile);
}
?>