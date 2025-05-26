<?php
$host = 'dpg-d0q2kleuk2gs73a63960-a.oregon-postgres.render.com';
$port = 5432;
$dbname = 'electronics_shop';
$username = 'electronics_shop_user';
$password = 'zSiCB74wM7hpHtqeyUDw1ewd2TOySz6U';


try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Подключение успешно!";

    // Пример простого запроса
    $stmt = $pdo->query("SELECT * FROM products LIMIT 5");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($products);
    echo "</pre>";

} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage();
}
?>
