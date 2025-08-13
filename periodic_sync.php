<?php
// Periodic sync script that can be run via cron or manually
require_once 'config.php';

$log_file = __DIR__ . '/sync.log';
$lock_file = __DIR__ . '/sync.lock';

function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
    echo "[$timestamp] $message\n";
}

// Check if sync is already running
if (file_exists($lock_file)) {
    $lock_time = filemtime($lock_file);
    if (time() - $lock_time < 300) { // 5 minutes
        log_message("Sync already running, skipping.");
        exit(0);
    } else {
        log_message("Removing stale lock file.");
        unlink($lock_file);
    }
}

// Create lock file
touch($lock_file);

try {
    log_message("Starting periodic IPO sync...");
    
    // Check if the main sync script exists
    if (!file_exists(__DIR__ . '/sync_api_ipos.php')) {
        log_message("ERROR: sync_api_ipos.php not found");
        exit(1);
    }
    
    // Capture output from sync script
    ob_start();
    include __DIR__ . '/sync_api_ipos.php';
    $sync_output = ob_get_clean();
    
    log_message("Sync output: " . trim($sync_output));
    log_message("Periodic sync completed successfully.");
    
} catch (Exception $e) {
    log_message("ERROR: Sync failed - " . $e->getMessage());
} finally {
    // Remove lock file
    if (file_exists($lock_file)) {
        unlink($lock_file);
    }
}

// Cleanup old log entries (keep last 1000 lines)
if (file_exists($log_file)) {
    $lines = file($log_file);
    if (count($lines) > 1000) {
        $lines = array_slice($lines, -1000);
        file_put_contents($log_file, implode('', $lines));
        log_message("Log file trimmed to 1000 lines.");
    }
}
?>