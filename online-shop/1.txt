<?php
// Параметры подключения к базе данных
$host = 'localhost';  // Адрес сервера MySQL (localhost, если база на том же сервере)
$dbname = 'electronics_shop';  // Название базы данных
$username = 'root';  // Имя пользователя базы данных
$password = 'zhe27';  // Пароль пользователя базы данных
$charset = 'utf8mb4';  // Кодировка для работы с базой данных

// Стартуем сессию только если Ыона еще не начата
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Строка подключения
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    
    // Опции для PDO
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Включаем исключения для ошибок
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Устанавливаем ассоциативный режим для получения данных
        PDO::ATTR_EMULATE_PREPARES => false,  // Отключаем эмуляцию подготовленных запросов для повышения безопасности
    ];

    // Создаем объект PDO для подключения к базе данных
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Проверка успешного подключения
    if ($pdo) {
        //echo "Подключение успешно!";
    }
} catch (PDOException $e) {
    // Если не удалось подключиться к базе данных, выводим сообщение об ошибке
    die("Ошибка подключения: " . $e->getMessage());
}

// Настройки сайта
define('SITE_NAME', 'TechShop');  // Название сайта
define('SITE_URL', 'http://localhost/online-shop');  // URL сайта
define('ADMIN_EMAIL', 'admin@techshop.ru');  // Электронная почта администратора

?>
