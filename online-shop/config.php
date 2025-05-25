<?php
$host = 'dpg-d0pk9todl3ps73asobi0-a';  // твой Hostname
$port = '5432';
$dbname = 'shopdb_vynx';                // твоя база
$user = 'shopdb_vynx_user';             // пользователь
$password = 'ВАШ_ПАРОЛЬ_ОТ_RENDER';    // пароль из Render

// Стартуем сессию, если еще не начата
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  
        PDO::ATTR_EMULATE_PREPARES => false,  
    ];

    $pdo = new PDO($dsn, $user, $password, $options);
    
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// Настройки сайта
define('SITE_NAME', 'TechShop');
define('SITE_URL', 'https://your-online-shop.onrender.com');
define('ADMIN_EMAIL', 'admin@techshop.ru');
?>
