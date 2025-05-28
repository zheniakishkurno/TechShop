<?php
require_once 'config.php';
require_once 'functions.php';

$searchQuery = trim($_GET['q'] ?? '');
$products = [];

if (!empty($searchQuery)) {
    $products = searchProductsByName($searchQuery);

    if (count($products) === 1) {
        $productId = $products[0]['id'];
        header("Location: product.php?id=" . urlencode($productId));
        exit;
    }
}


$page_title = 'Результаты поиска: ' . htmlspecialchars($searchQuery);
require_once 'header.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>
    
<div class="search-results-page">
    <div class="container">
        <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="index.php">Главная</a> / 
            <a href="catalog.php">Каталог</a> / 
            <span>Поиск: "<?= htmlspecialchars($searchQuery) ?>"</span>
        </div>

        <!-- Результаты поиска -->
        <h1 class="search-title">Результаты поиска: "<?= htmlspecialchars($searchQuery) ?>"</h1>
        
        <?php if (!empty($products)): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($product['image_url'] ?? 'images/no-image.jpg') ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php if (!empty($product['discount'])): ?>
                                <span class="discount-badge">-<?= $product['discount'] ?>%</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-info">
                            <h3><?= highlightSearchQuery($product['name'], $searchQuery) ?></h3>
                            
                            <div class="product-meta">
                                <div class="availability <?= ($product['stock'] > 0) ? 'in-stock' : 'out-of-stock' ?>">
                                    <?= ($product['stock'] > 0) ? 'В наличии' : 'Нет в наличии' ?>
                                </div>
                                <div class="rating">
                                    <span class="stars">★★★★★</span>
                                    <span class="reviews">(<?= $product['reviews_count'] ?? 0 ?>)</span>
                                </div>
                            </div>
                            
                            <div class="product-price">
                                <?php if (!empty($product['discount'])): ?>
                                    <span class="old-price"><?= number_format($product['price'], 2, '.', ' ') ?> ₽</span>
                                    <span class="current-price"><?= number_format($product['price'] * (1 - $product['discount'] / 100), 2, '.', ' ') ?> ₽</span>
                                <?php else: ?>
                                    <span class="current-price"><?= number_format($product['price'], 2, '.', ' ') ?> ₽</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-actions">
                                <div class="quantity-control">
                                    <button class="quantity-btn minus">-</button>
                                    <input type="number" class="quantity-input" value="1" 
                                           min="1" max="<?= $product['stock'] ?>" 
                                           data-id="<?= $product['id'] ?>">
                                    <button class="quantity-btn plus">+</button>
                                </div>
                                <button class="btn add-to-cart"
                                        data-id="<?= $product['id'] ?>"
                                        data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                                        data-price="<?= !empty($product['discount']) 
                                            ? $product['price'] * (1 - $product['discount'] / 100)
                                            : $product['price'] ?>">
                                    В корзину
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p>По вашему запросу "<?= htmlspecialchars($searchQuery) ?>" ничего не найдено.</p>
                <a href="catalog.php" class="btn">Перейти в каталог</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Добавляем необходимые скрипты -->
<script>
// Обработчики для кнопок количества
document.querySelectorAll('.quantity-btn').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.quantity-input');
        if (this.classList.contains('minus') && input.value > 1) {
            input.value--;
        } else if (this.classList.contains('plus')) {
            const max = parseInt(input.getAttribute('max')) || 999;
            if (input.value < max) input.value++;
        }
    });
});
</script>

<script src="profile.js"></script>

<?php require_once 'footer.php'; ?>
<style>
    .product-card {
        width: 220px;
        padding: 10px;
    }
    .product-image img {
        max-height: 140px;
    }
    .product-info h3 {
        font-size: 14px;
    }
    .product-price {
        font-size: 14px;
    }
    .quantity-control {
        font-size: 12px;
    }
    .product-actions .btn {
        font-size: 13px;
        padding: 6px 10px;
    }
</style>
