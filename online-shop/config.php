<?php
// Параметры подключения к базе данных из переменных окружения
$host = getenv('DB_HOST') ?: 'localhost';
$dbname   = getenv('DB_NAME') ?: 'electronics_shop';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'zhe27';
$charset = 'utf8mb4';

// Стартуем сессию, если еще не начата
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  
        PDO::ATTR_EMULATE_PREPARES => false,  
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// Настройки сайта — можешь оставить как есть или подправить под prod-среду
define('SITE_NAME', 'TechShop');
// Сделай SITE_URL пустым или укажи URL на Render, например:
define('SITE_URL', 'https://your-online-shop.onrender.com');
define('ADMIN_EMAIL', 'admin@techshop.ru');
?>
