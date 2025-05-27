<?php
session_start();  // Начинаем сессию, если это еще не сделано

require_once 'header.php';

$page_title = 'Успешный заказ';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>
<div class="order-success-page">
    <div class="container">
        <h1>Ваш заказ успешно оформлен!</h1>

        <?php if (isset($_SESSION['order_success']) && $_SESSION['order_success']): ?>
            <div class="alert success">
                <p>Спасибо за покупку! Ваш заказ был успешно оформлен. Номер вашего заказа: <?= htmlspecialchars($_SESSION['order_id']) ?>.</p>
            </div>
            <?php 
                unset($_SESSION['order_success'], $_SESSION['order_id']);  // Убираем флаг и ID заказа после вывода сообщения
            ?>
        <?php else: ?>
            <div class="alert error">
                <p>Что-то пошло не так. Попробуйте снова.</p>
            </div>
        <?php endif; ?>
        
        <a href="index.php#products" class="btn">Перейти в каталог</a>
    </div>
</div>

<?php require_once 'footer.php'; ?>
