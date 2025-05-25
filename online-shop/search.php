<?php
require_once 'config.php';
require_once 'functions.php';

$searchQuery = $_GET['q'] ?? '';
$products = [];

if ($searchQuery) {
    $products = searchProductsByName($searchQuery);  // Using the function to search products by name
}

$page_title = 'Результаты поиска';
require_once 'header.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TechShop - лучший магазин электроники с огромным выбором гаджетов по доступным ценам.">
    <title><?= isset($page_title) ? "$page_title | " : "" ?>TechShop</title>
    
    <!-- Подключение CSS файла -->
    <link rel="stylesheet" href="/online-shop/css/style.css"> <!-- Correct the path to your CSS file -->
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<div class="product-page">
    <div class="container">
        <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="index.php">Главная</a> / 
            <a href="catalog.php">Каталог</a> / 
            <span>Результаты поиска</span>
        </div>

        <!-- Список товаров -->
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-details">
                    <!-- Галерея товара -->
                    <div class="product-gallery">
                        <div class="main-image">
                            <img src="<?= htmlspecialchars($product['image_url'] ?? 'images/no-image.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                    </div>

                    <!-- Информация о товаре -->
                    <div class="product-info">
                        <h1><?= htmlspecialchars($product['name']) ?></h1>

                        <div class="product-meta">
                            <div class="rating">
                                <span class="stars">★★★★★</span>
                                <span class="reviews">
                                    <?= isset($product['reviews_count']) ? $product['reviews_count'] : 0 ?> отзывов
                                </span>
                            </div>
                            
                            <div class="availability <?= isset($product['stock']) && $product['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                                <?= isset($product['stock']) && $product['stock'] > 0 ? 'В наличии' : 'Нет в наличии' ?>
                            </div>
                        </div>
                        
                        <div class="product-price">
                            <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                                <span class="old-price"><?= number_format($product['price'], 2, '.', ' ') ?> ₽</span>
                                <span class="current-price"><?= number_format($product['price'] * (1 - $product['discount']/100), 2, '.', ' ') ?> ₽</span>
                                <span class="discount">-<?= $product['discount'] ?>%</span>
                            <?php else: ?>
                                <span class="current-price"><?= number_format($product['price'], 2, '.', ' ') ?> ₽</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Количество товара -->
                        <div class="product-actions">
                    <div class="quantity">
                        <button class="quantity-btn minus">-</button>
                        <input type="number" value="1" min="1" max="<?= isset($product['stock']) ? $product['stock'] : 0 ?>" id="quantity-input">
                        <button class="quantity-btn plus">+</button>
                    </div>
                    
                    <button class="btn add-to-cart" data-id="<?= $product['id'] ?>">В корзину</button>
                </div>

                        
                        <div class="product-description">
                            <h3>Описание</h3>
                            <p><?= nl2br(htmlspecialchars($product['description'] ?? 'Описание отсутствует')) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Товары не найдены.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Подключение JavaScript файла -->
<script src="js/product.js"></script> <!-- Укажите правильный путь к вашему файлу JS -->

<?php require_once 'footer.php'; ?> 
