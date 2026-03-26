<?php
// db.php - MySQL connection.
$host = 'localhost';
$dbname = 'mutcu_library';
$username = 'root';
$password = '';

try {
    // Create database if not exists
    $tempPdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $tempPdo = null;

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL DEFAULT 'member',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL,
        description TEXT,
        cover VARCHAR(500),
        drive_link VARCHAR(500) NOT NULL,
        added_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        download_count INT DEFAULT 0,
        view_count INT DEFAULT 0
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS articles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        abstract TEXT,
        link VARCHAR(500) NOT NULL,
        date VARCHAR(50),
        read_time VARCHAR(50),
        added_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        view_count INT DEFAULT 0
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        event_type VARCHAR(50),
        target_type VARCHAR(50),
        target_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // Seed admin if not exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['admin@mutcu.local']);
    if (!$stmt->fetch()) {
        $pdo->exec("INSERT INTO users (name,email,password,role) VALUES ('Admin','admin@mutcu.local','" . password_hash('mutcu123', PASSWORD_DEFAULT) . "','admin');");
    }

} catch (Exception $e) {
    exit('Database error: ' . htmlspecialchars($e->getMessage()));
}
