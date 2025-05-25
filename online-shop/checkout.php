<?php
session_start();
require_once 'functions.php';

// Если корзина пуста, перенаправляем в каталог
if (empty($_SESSION['cart'])) {
    header('Location: catalog.php');
    exit;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация и обработка заказа через API
    // JavaScript отправит AJAX запрос к api.php?action=place_order
}

// Получаем общую стоимость для отображения
$productIds = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));
$products = $db->query(
    "SELECT id, price FROM products WHERE id IN ($placeholders)", 
    $productIds
)->fetchAll();

$subtotal = 0;
foreach ($products as $product) {
    $quantity = $_SESSION['cart'][$product['id']];
    $subtotal += $product['price'] * $quantity;
}

$tax = $subtotal * 0.05;
$shipping = $subtotal > 5000 ? 0 : 500;
$total = $subtotal + $tax + $shipping;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <div class="checkout-page">
            <h1>Оформление заказа</h1>
            
            <div class="checkout-layout">
                <div class="checkout-form-container">
                    <form id="checkout-form" class="checkout-form">
                        <div class="checkout-section">
                            <div class="section-header">
                                <div class="section-number">1</div>
                                <h2 class="section-title">Контактная информация</h2>
                            </div>
                            
                            <div class="form-group">
                                <label for="name">ФИО *</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Телефон *</label>
                                <input type="tel" id="phone" name="phone" required>
                            </div>
                        </div>
                        
                        <div class="checkout-section">
                            <div class="section-header">
                                <div class="section-number">2</div>
                                <h2 class="section-title">Адрес доставки</h2>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Адрес *</label>
                                <textarea id="address" name="address" rows="3" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="comment">Комментарий к заказу</label>
                                <textarea id="comment" name="comment" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <div class="checkout-section">
                            <div class="section-header">
                                <div class="section-number">3</div>
                                <h2 class="section-title">Способ оплаты</h2>
                            </div>
                            
                            <div class="payment-methods">
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="cash" checked>
                                    <div class="payment-content">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>Наличными при получении</span>
                                    </div>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="card">
                                    <div class="payment-content">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Банковской картой онлайн</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">Подтвердить заказ</button>
                    </form>
                </div>
                
                <div class="checkout-summary">
                    <div class="checkout-summary-container">
                        <h3>Ваш заказ</h3>
                        
                        <div class="order-items">
                            <?php foreach ($products as $product): ?>
                                <?php $quantity = $_SESSION['cart'][$product['id']]; ?>
                                <div class="order-item">
                                    <div class="order-item-name">
                                        <span class="quantity"><?= $quantity ?> ×</span>
                                        <span>Товар #<?= $product['id'] ?></span>
                                    </div>
                                    <div class="order-item-price"><?= number_format($product['price'] * $quantity, 2) ?> руб.</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-totals">
                            <div class="order-total-row">
                                <span>Подытог:</span>
                                <span><?= number_format($subtotal, 2) ?> руб.</span>
                            </div>
                            
                            <div class="order-total-row">
                                <span>Доставка:</span>
                                <span><?= $shipping === 0 ? 'Бесплатно' : number_format($shipping, 2) . ' руб.' ?></span>
                            </div>
                            
                            <div class="order-total-row">
                                <span>Налог (5%):</span>
                                <span><?= number_format($tax, 2) ?> руб.</span>
                            </div>
                            
                            <div class="order-total-row grand-total">
                                <span>Итого:</span>
                                <span><?= number_format($total, 2) ?> руб.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <script src="js/checkout.js"></script>
</body>
</html>