<?php
$host = 'dpg-d0q2kleuk2gs73a63960-a.oregon-postgres.render.com';
$dbname = 'electronics_shop';
$username = 'electronics_shop_user';
$password = 'zSiCB74wM7hpHtqeyUDw1ewd2TOySz6U';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

define('SITE_NAME', 'TechShop');
define('SITE_URL', 'https://example.onrender.com');
define('ADMIN_EMAIL', 'admin@techshop.ru');
?>
