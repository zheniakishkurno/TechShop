<?php
require_once 'config.php';
require_once 'functions.php';

$searchQuery = $_GET['q'] ?? '';
$products = [];

if ($searchQuery) {
    $products = searchProductsByName($searchQuery);
}

$page_title = 'Результаты поиска';
require_once 'header.php'; // тут уже есть <html><head><body>
?>

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
                                <span class="reviews"><?= $product['reviews_count'] ?? 0 ?> отзывов</span>
                            </div>
                            <div class="availability <?= ($product['stock'] ?? 0) > 0 ? 'in-stock' : 'out-of-stock' ?>">
                                <?= ($product['stock'] ?? 0) > 0 ? 'В наличии' : 'Нет в наличии' ?>
                            </div>
                        </div>

                        <div class="product-price">
                            <?php if (!empty($product['discount'])): ?>
                                <span class="old-price"><?= number_format($product['price'], 2, '.', ' ') ?> ₽</span>
                                <span class="current-price"><?= number_format($product['price'] * (1 - $product['discount'] / 100), 2, '.', ' ') ?> ₽</span>
                                <span class="discount">-<?= $product['discount'] ?>%</span>
                            <?php else: ?>
                                <span class="current-price"><?= number_format($product['price'], 2, '.', ' ') ?> ₽</span>
                            <?php endif; ?>
                        </div>

                        <div class="product-actions">
                            <div class="quantity">
                                <button class="quantity-btn minus">-</button>
                                <input type="number" class="quantity-input" value="1"
                                    min="1"
                                    max="<?= $product['stock'] ?? 0 ?>"
                                    data-id="<?= $product['id'] ?>">
                                <button class="quantity-btn plus">+</button>
                            </div>

                            <button class="btn add-to-cart"
                                data-id="<?= $product['id'] ?>"
                                data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                                data-price="<?= isset($product['discount']) && $product['discount'] > 0
                                    ? number_format($product['price'] * (1 - $product['discount'] / 100), 2, '.', '')
                                    : number_format($product['price'], 2, '.', '') ?>">
                                В корзину
                            </button>
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

<?php require_once 'footer.php'; ?>
