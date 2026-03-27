<?php
// ==========================================
// HOSTINGER DATABASE CONFIGURATION
// ==========================================

// In Hostinger, the host is usually 'localhost'. 
// If you get a connection error, check your hPanel Databases section to see if it specifies something like 'db.hostinger.com'
$host = 'localhost'; 
$dbname = 'mutcu_library';
$username = 'MutcuSec';
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
    die("<h3>Service Temporarily Unavailable</h3><p>We are currently performing maintenance on the database. Please try again in a few minutes.</p>");
}
?>