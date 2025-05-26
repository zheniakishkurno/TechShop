<?php
require_once 'config.php'; 
require_once 'functions.php';

$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$total = 0;

foreach ($cart as $product_id => $quantity) {
    $product = getProductById($product_id);
    if ($product) {
        $product['quantity'] = $quantity;
        $product['subtotal'] = $product['price'] * $quantity;
        $cart_items[] = $product;
        $total += $product['subtotal'];
    }
}

// В обработчике оформления заказа:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = 'cart.php';
        header('Location: login.php');
        exit;
    }
    
    if (empty($cart_items)) {
        $error = 'Ваша корзина пуста';
    } else {
        // Проверяем адрес для курьерской/почтовой доставки
        $address = '';
        if (in_array($_POST['delivery_method'], ['courier', 'post'])) {
            $address = $_POST['address'] ?? '';
            if (empty($address)) {
                $error = 'Укажите адрес доставки';
            }
        }
        
        if (!isset($error)) {
            $result = createOrder(
                $_SESSION['user_id'],
                array_map(function($item) {
                    return [
                        'id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price']
                    ];
                }, $cart_items),
                $total,
                $_POST['payment_method'],
                $_POST['delivery_method'],
                $address
            );
            
            if ($result['success']) {
                unset($_SESSION['cart']);
                $_SESSION['order_success'] = true;
                $_SESSION['order_id'] = $result['order_id'];
                header('Location: order_success.php');
                exit;
            } else {
                $error = $result['message'] ?? 'Ошибка при оформлении заказа';
            }
        }
    }
}

$page_title = 'Корзина';
require_once 'header.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TechShop - лучший магазин электроники с огромным выбором гаджетов по доступным ценам.">
    <title>TechShop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="cart-page">
    <div class="container">
        <h1>Корзина</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Ваша корзина пуста</h2>
                <p>Перейдите в каталог, чтобы добавить товары</p>
                <a href="index.php#products" class="btn">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <table>
                        <thead>
                            <tr>
                                <th>Товар</th>
                                <th>Цена</th>
                                <th>Количество</th>
                                <th>Сумма</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td class="product-info">
                                        <img src="<?= htmlspecialchars($item['image_url'] ?? 'images/no-image.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        <div>
                                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                                            <div class="availability <?= $item['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                                                <?= $item['stock'] > 0 ? 'В наличии' : 'Нет в наличии' ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price"><?= number_format($item['price'], 2, '.', ' ') ?> ₽</td>
                                    <td class="quantity">
                                        <input type="number" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" data-id="<?= $item['id'] ?>">
                                    </td>
                                    <td class="subtotal"><?= number_format($item['subtotal'], 2, '.', ' ') ?> ₽</td>
                                    <td class="remove">
                                        <button class="remove-item" data-id="<?= $item['id'] ?>"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-box">
                        <h3>Итого</h3>
                        
                        <div class="summary-row">
                            <span>Товары (<?= count($cart_items) ?>)</span>
                            <span><?= number_format($total, 2, '.', ' ') ?> ₽</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Доставка</span>
                            <span>Бесплатно</span>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Общая сумма</span>
                            <span><?= number_format($total, 2, '.', ' ') ?> ₽</span>
                        </div>
                        
                        <form method="POST" class="checkout-form">
    <!-- Способ оплаты -->
    <div class="form-group">
        <label for="payment_method">Способ оплаты:</label>
        <select id="payment_method" name="payment_method" required>
            <option value="">Выберите способ оплаты</option>
            <option value="card">Банковская карта</option>
            <option value="cash">Наличные при получении</option> 
            <option value="online">Онлайн оплата</option>
        </select>
    </div>
    
    <!-- Способ доставки -->
    <div class="form-group"> 
        <label for="delivery_method">Способ доставки:</label>
        <select id="delivery_method" name="delivery_method" required>
            <option value="">Выберите способ доставки</option>
            <option value="courier">Курьерская доставка</option>
            <option value="pickup">Самовывоз</option>
            <option value="post">Почтовая доставка</option>
        </select>
    </div>
    
    <!-- Поле для адреса (появляется при выборе курьерской/почтовой доставки) -->
    <div class="form-group address-field" style="display: none;">
        <label for="address">Адрес доставки:</label>
        <input type="text" id="address" name="address" readonly>
    </div>
    
    <button type="submit" name="checkout" class="btn">Оформить заказ</button>
</form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="js/cart.js"></script>
<?php require_once 'footer.php'; ?>

<script>
// Показываем поле адреса при выборе курьерской/почтовой доставки
document.getElementById('delivery_method').addEventListener('change', function() {
    const addressField = document.querySelector('.address-field');
    const addressInput = addressField.querySelector('input');
    
    if (this.value === 'courier' || this.value === 'post') {
        addressField.style.display = 'block';
        addressInput.required = true;
        addressInput.readOnly = false;
        addressInput.value = '';
    } else if (this.value === 'pickup') {
        addressField.style.display = 'block';
        addressInput.required = false;
        addressInput.readOnly = true;
        addressInput.value = 'Уручье 4';
    } else {
        addressField.style.display = 'none';
        addressInput.required = false;
        addressInput.readOnly = false;
        addressInput.value = '';
    }
});

</script>
