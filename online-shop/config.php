<?php
$host = 'dpg-d15vvs7diees73eiqul0-a.oregon-postgres.render.com';
$dbname = 'electronics_shop';
$user = ' electronics_shop_d7t7_user';
$password = 'NA3a44kFrN8KQg4H4zP8TEqdUZFeOhAc';

$dsn = "pgsql:host=$host;dbname=$dbname;sslmode=require";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // выбрасывать исключения при ошибках
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // возвращать ассоциативные массивы
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
