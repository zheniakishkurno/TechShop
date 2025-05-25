<?php
require_once 'functions.php';
$current_user = getCurrentUser();

header('Content-Type: application/json');

if (!$current_user) {
    echo json_encode(['success' => false, 'message' => 'Вы не авторизованы']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];

    // Проверим, принадлежит ли заказ текущему пользователю
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT o.id FROM orders o
        JOIN customers c ON o.customer_id = c.id
        JOIN users u ON c.email = u.email
        WHERE o.id = ? AND u.id = ?
    ");
    $stmt->execute([$order_id, $current_user['id']]);

    if ($stmt->fetch()) {
        $update = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $update->execute([$order_id]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Заказ не найден или не принадлежит вам']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос']);
}
