<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        username VARCHAR(50) UNIQUE, 
        password_hash VARCHAR(255)
    )");

    $password = password_hash('Admin@123', PASSWORD_DEFAULT);
    
    // Check if exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES ('admin', ?)");
        $stmt->execute([$password]);
        echo "Default admin user 'admin' seeded.";
    } else {
        echo "Admin user already exists.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
