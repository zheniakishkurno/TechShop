<?php
// Получаем параметры подключения из переменных среды
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$charset = 'utf8mb4';

// Стартуем сессию, если еще не запущена
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
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Настройки сайта
define('SITE_NAME', 'TechShop');
define('SITE_URL', getenv('SITE_URL') ?: 'https://your-render-url.onrender.com');
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@techshop.ru');
?>
