<?php
require_once __DIR__ . '/../config/config.php';

// One-time setup: create `admins` table and insert default admin if missing.
// WARNING: Remove this file after running.

try {
    $sql = "CREATE TABLE IF NOT EXISTS `admins` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `username` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);
} catch (Exception $e) {
    echo "Error creating table: " . htmlspecialchars($e->getMessage());
    exit;
}

$defaultUser = 'admin';
$defaultPass = 'admin123';

try {
    $stmt = $conn->prepare('SELECT id FROM admins WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $defaultUser]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error checking existing admin: " . htmlspecialchars($e->getMessage());
    exit;
}

if ($exists) {
    echo "Admin user 'admin' already exists (id: " . intval($exists['id']) . ").\n";
    echo "You may delete this script now.";
    exit;
}

$hash = password_hash($defaultPass, PASSWORD_DEFAULT);
try {
    $i = $conn->prepare('INSERT INTO admins (username, password) VALUES (:u, :p)');
    $i->execute([':u' => $defaultUser, ':p' => $hash]);
    echo "Inserted default admin user 'admin' with password 'admin123'.\n";
    echo "Please delete admin/setup_admin_table.php now for security.";
} catch (Exception $e) {
    echo "Error inserting admin user: " . htmlspecialchars($e->getMessage());
}

?>
