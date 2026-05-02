<?php
/**
 * One-time database setup script.
 * Run via browser: http://localhost/project/Inventory/public/setup_db.php
 * DELETE THIS FILE after successful setup.
 */
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `inventory_ms` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<h2 style='color:green;font-family:sans-serif'>✅ Database `inventory_ms` created successfully!<br>You can now delete this file and run: <code>php artisan migrate</code></h2>";
} catch (PDOException $e) {
    echo "<h2 style='color:red;font-family:sans-serif'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
