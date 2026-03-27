<?php
// ==========================================
// HOSTINGER DATABASE CONFIGURATION
// ==========================================

// Hostinger Account Prefix: u887119320_
$host = 'localhost'; // Usually 'localhost'. If it fails, check hPanel for 'MySQL Host'
$dbname = 'u887119320_mutcu_library'; // Exact Hostinger DB Name
$username = 'u887119320_MutcuSec'; // Exact Hostinger DB User
$password = 'MutcuSec@2026';

try {
    // Create the PDO connection with UTF-8 encoding
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Disable emulated prepares for better security against SQL injection
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    // In production, we log the error silently instead of showing it to the user for security reasons
    error_log("Database Connection Error: " . $e->getMessage());
    
    // TEMPORARY DEBUGGING FIX: 
    // This will print the exact MySQL error on your screen so you know exactly what is wrong.
    die("<h3>Database Connection Failed!</h3>
         <p><strong>Exact Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
         <p><em>Hint: If it says 'Access Denied', double check the password. If it says 'Connection Refused', change \$host in db.php from 'localhost' to the exact MySQL Host IP shown in hPanel.</em></p>");
}
?>