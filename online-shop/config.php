<?php
$host = 'dpg-d0q2kleuk2gs73a63960-a.oregon-postgres.render.com';
$dbname = 'electronics_shop';
$user = 'electronics_shop_user'; // реальный пользователь из Render
$password = 'zSiCB74wM7hpHtqeyUDw1ewd2TOySz6U'; // реальный пароль из Render

$dsn = "pgsql:host=$host;dbname=$dbname;sslmode=require";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
