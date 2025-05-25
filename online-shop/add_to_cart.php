<?php
session_start();

// Получаем данные из POST запроса
$data = json_decode(file_get_contents('php://input'), true);

// Проверяем, что данные были переданы
if (isset($data['product_id']) && isset($data['quantity'])) {
    $product_id = (int)$data['product_id'];
    $quantity = (int)$data['quantity'];

    // Проверяем, если товар уже есть в корзине
    if (isset($_SESSION['cart'][$product_id])) {
        // Если товар уже в корзине, увеличиваем количество
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        // Если товара нет в корзине, добавляем его
        $_SESSION['cart'][$product_id] = $quantity;
    }

    // Считаем общее количество товаров в корзине
    $total_items = array_sum($_SESSION['cart']);

    // Отправляем ответ с количеством товаров в корзине
    echo json_encode(['success' => true, 'total_items' => $total_items]);
} else {
    // Если данные некорректны, возвращаем ошибку
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
?>
