<?php
// db.php - Auto-creates the table if it doesn't exist

$host     = 'sql100.infinityfree.com';
$dbname   = 'if0_41604407_petinformation';
$username = 'if0_41604407';
$password = 'PQ327KKcBkxKsb';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
        $username, 
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Auto-create the pets table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS `if0_41604407_pets` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `species` VARCHAR(50) NOT NULL,
            `breed` VARCHAR(100) NOT NULL,
            `age` INT NOT NULL,
            `owner_name` VARCHAR(100) NOT NULL,
            `image` VARCHAR(255) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($createTableSQL);

} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

define('TABLE_PETS', 'if0_41604407_pets');
?>