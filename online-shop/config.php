<?php
$host = 'dpg-d0q2kleuk2gs73a63960-a.oregon-postgres.render.com';
$port = 5432;
$dbname = 'electronics_shop';
$username = 'electronics_shop_user';
$password = 'zSiCB74wM7hpHtqeyUDw1ewd2TOySz6U';


// Подключение
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Подключение успешно!";
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>
