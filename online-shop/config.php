<?php
// config.php

$host = 'dpg-d0q2kleuk2gs73a63960-a.oregon-postgres.render.com';
$dbname = 'online-shop';
$user = 'твой_пользователь';  // замени на имя пользователя БД
$password = 'твой_пароль';    // замени на пароль БД

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage();
    exit;
}
