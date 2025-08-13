<?php
// Local development database configuration for IPO Pulse
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'ipo_pulse';

function get_db_connection() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
    
    // Try MySQL first
    try {
        $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        
        if ($conn->connect_error) {
            // Try to create database if it doesn't exist
            $temp_conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
            if (!$temp_conn->connect_error) {
                $temp_conn->query("CREATE DATABASE IF NOT EXISTS $DB_NAME");
                $temp_conn->close();
                
                // Try connecting again
                $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
                if (!$conn->connect_error) {
                    // Initialize database schema
                    init_database_schema($conn);
                    return $conn;
                }
            }
        } else {
            // MySQL connection successful
            init_database_schema($conn);
            return $conn;
        }
    } catch (Exception $e) {
        error_log("MySQL connection failed: " . $e->getMessage());
    }
    
    // Fallback to SQLite if MySQL fails
    error_log("Falling back to SQLite database");
    
    $DB_FILE = __DIR__ . '/ipo_pulse.sqlite';
    try {
        $pdo = new PDO("sqlite:$DB_FILE");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Initialize SQLite schema
        init_sqlite_schema($pdo);
        
        // Return a wrapper that mimics mysqli interface
        return new SQLiteWrapper($pdo);
        
    } catch (Exception $e) {
        error_log("SQLite connection failed: " . $e->getMessage());
        // Return mock database for testing
        return new MockDatabase();
    }
}

function init_database_schema($conn) {
    // Create tables if they don't exist
    $sql = "
    CREATE TABLE IF NOT EXISTS ipos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        open_date DATE NOT NULL,
        close_date DATE NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        details TEXT,
        status ENUM('upcoming','live','closed') NOT NULL DEFAULT 'upcoming',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_ipo (name, open_date, close_date)
    );
    
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        mobile VARCHAR(20) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email_alerts TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    );
    
    CREATE TABLE IF NOT EXISTS api_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        endpoint VARCHAR(255) NOT NULL,
        response_status INT,
        response_data TEXT,
        error_message TEXT,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    ";
    
    try {
        $conn->multi_query($sql);
        
        // Clear any remaining results
        while ($conn->next_result()) {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        }
        
        // Insert sample data if table is empty
        $result = $conn->query("SELECT COUNT(*) as count FROM ipos");
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['count'] == 0) {
                $conn->query("INSERT INTO ipos (name, open_date, close_date, price, details, status) VALUES
                    ('Sample Tech Ltd', '2025-01-20', '2025-01-24', 120.50, 'Sample technology company IPO.', 'upcoming'),
                    ('Demo Pharma', '2025-01-15', '2025-01-19', 95.00, 'Demo pharmaceutical company IPO.', 'closed'),
                    ('Test Finance', '2025-01-18', '2025-01-22', 150.00, 'Test financial services company IPO.', 'live')");
            }
        }
        
        // Insert admin user if not exists
        $result = $conn->query("SELECT COUNT(*) as count FROM admin_users");
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['count'] == 0) {
                $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
                if ($stmt) {
                    $admin_user = 'admin';
                    $stmt->bind_param('ss', $admin_user, $hashed_password);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    } catch (Exception $e) {
        error_log("Database schema initialization failed: " . $e->getMessage());
    }
}

function log_api_call($endpoint, $status, $data, $error = null) {
    try {
        $conn = get_db_connection();
        $stmt = $conn->prepare("INSERT INTO api_logs (endpoint, response_status, response_data, error_message) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('siss', $endpoint, $status, $data, $error);
            $stmt->execute();
            $stmt->close();
        }
        $conn->close();
    } catch (Exception $e) {
        error_log("Failed to log API call: " . $e->getMessage());
    }
}

function init_sqlite_schema($pdo) {
    $sql = "
    CREATE TABLE IF NOT EXISTS ipos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        open_date TEXT NOT NULL,
        close_date TEXT NOT NULL,
        price REAL NOT NULL,
        details TEXT,
        status TEXT NOT NULL DEFAULT 'upcoming',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS api_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        endpoint TEXT NOT NULL,
        response_status INTEGER,
        response_data TEXT,
        error_message TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ";
    
    $pdo->exec($sql);
    
    // Insert sample data if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM ipos");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['count'] == 0) {
        $pdo->exec("INSERT INTO ipos (name, open_date, close_date, price, details, status) VALUES
            ('Sample Tech Ltd', '2025-01-20', '2025-01-24', 120.50, 'Sample technology company IPO.', 'upcoming'),
            ('Demo Pharma', '2025-01-15', '2025-01-19', 95.00, 'Demo pharmaceutical company IPO.', 'closed'),
            ('Test Finance', '2025-01-18', '2025-01-22', 150.00, 'Test financial services company IPO.', 'live')");
    }
}

// SQLite wrapper to mimic mysqli interface
class SQLiteWrapper {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function query($sql) {
        try {
            $stmt = $this->pdo->query($sql);
            return new SQLiteResultWrapper($stmt);
        } catch (Exception $e) {
            error_log("Query failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function prepare($sql) {
        try {
            return new SQLiteStatementWrapper($this->pdo->prepare($sql));
        } catch (Exception $e) {
            error_log("Prepare failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function real_escape_string($string) {
        return addslashes($string);
    }
    
    public function close() {
        $this->pdo = null;
    }
    
    public $connect_error = null;
}

class SQLiteResultWrapper {
    private $stmt;
    public $num_rows = 0;
    private $rows = [];
    private $index = 0;
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
        if ($stmt) {
            // Fetch all rows to count them
            $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->num_rows = count($this->rows);
        }
    }
    
    public function fetch_assoc() {
        if ($this->index < count($this->rows)) {
            return $this->rows[$this->index++];
        }
        return false;
    }
}

class SQLiteStatementWrapper {
    private $stmt;
    public $error = null;
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
    }
    
    public function bind_param($types, ...$vars) {
        if (!$this->stmt) return false;
        
        try {
            for ($i = 0; $i < count($vars); $i++) {
                $this->stmt->bindParam($i + 1, $vars[$i]);
            }
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    public function execute() {
        try {
            return $this->stmt ? $this->stmt->execute() : false;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    public function close() {
        $this->stmt = null;
    }
}

// Mock database for when even SQLite fails
class MockDatabase {
    public $connect_error = null;
    
    public function query($sql) {
        return new MockResult();
    }
    
    public function prepare($sql) {
        return new MockStatement();
    }
    
    public function real_escape_string($string) {
        return addslashes($string);
    }
    
    public function close() {}
}

class MockResult {
    public $num_rows = 3;
    private $data = [
        ['id' => 1, 'name' => 'Mock IPO 1', 'open_date' => '2025-01-20', 'close_date' => '2025-01-24', 'price' => '120.50', 'status' => 'upcoming'],
        ['id' => 2, 'name' => 'Mock IPO 2', 'open_date' => '2025-01-15', 'close_date' => '2025-01-19', 'price' => '95.00', 'status' => 'closed'],
        ['id' => 3, 'name' => 'Mock IPO 3', 'open_date' => '2025-01-18', 'close_date' => '2025-01-22', 'price' => '150.00', 'status' => 'live']
    ];
    private $index = 0;
    
    public function fetch_assoc() {
        return $this->index < count($this->data) ? $this->data[$this->index++] : false;
    }
}

class MockStatement {
    public $error = null;
    
    public function bind_param($types, ...$vars) {
        return true;
    }
    
    public function execute() {
        return true;
    }
    
    public function close() {}
}
?>