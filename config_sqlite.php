<?php
// Fallback database configuration using SQLite for local development
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'ipo_pulse';
$DB_FILE = __DIR__ . '/ipo_pulse.sqlite';

function get_db_connection() {
    global $DB_FILE;
    
    try {
        // Try SQLite for local development
        $pdo = new PDO("sqlite:$DB_FILE");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Initialize database schema for SQLite
        init_sqlite_schema($pdo);
        
        // Return a wrapper that mimics mysqli interface
        return new SQLiteWrapper($pdo);
        
    } catch (Exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        // Return mock database for testing
        return new MockDatabase();
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
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
        if ($stmt) {
            $this->num_rows = $stmt->rowCount();
        }
    }
    
    public function fetch_assoc() {
        return $this->stmt ? $this->stmt->fetch(PDO::FETCH_ASSOC) : false;
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

function log_api_call($endpoint, $status, $data, $error = null) {
    try {
        $conn = get_db_connection();
        if (method_exists($conn, 'prepare')) {
            $stmt = $conn->prepare("INSERT INTO api_logs (endpoint, response_status, response_data, error_message) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('siss', $endpoint, $status, $data, $error);
                $stmt->execute();
                $stmt->close();
            }
        }
        $conn->close();
    } catch (Exception $e) {
        error_log("Failed to log API call: " . $e->getMessage());
    }
}
?>