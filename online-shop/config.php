<?php
$host = 'dpg-d0q2kleuk2gs73a63960-a';
$dbname = 'electronics_shop';
$username = 'electronics_shop_user';
$password = 'zSiCB74wM7hpHtqeyUDw1ewd2TOySz6U';
$port = 5432;

// Стартуем сессию только если она еще не начата
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

    if ($pdo) {
        // echo "Подключение успешно!";
    }
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// Настройки сайта
define('SITE_NAME', 'TechShop');
define('SITE_URL', 'https://your-site-name.onrender.com');  // <-- Замени на URL сайта
define('ADMIN_EMAIL', 'admin@techshop.ru');
?>
