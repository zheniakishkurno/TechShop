<?php
$host = 'dpg-d0q2kleuk2gs73a63960-a.oregon-postgres.render.com';
$dbname = 'electronics_shop';
$user = 'electronics_shop_user';
$password = 'zSiCB74wM7hpHtqeyUDw1ewd2TOySz6U';

$dsn = "pgsql:host=$host;dbname=$dbname;sslmode=require";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // выбрасывать исключения при ошибках
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // возвращать ассоциативные массивы
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
