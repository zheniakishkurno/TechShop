<?php
require_once 'config.php';
require_once 'functions.php';

$searchQuery = $_GET['q'] ?? '';
$products = [];

if ($searchQuery) {
    $products = searchProductsByName($searchQuery);
}

$page_title = 'Результаты поиска: ' . htmlspecialchars($searchQuery);
require_once 'header.php';
?>

<div class="search-page">
    <div class="container">
        <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="index.php">Главная</a> / 
            <span>Результаты поиска: "<?= htmlspecialchars($searchQuery) ?>"</span>
        </div>

        <!-- Список товаров в виде карточек -->
        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <!-- Изображение товара -->
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($product['image_url'] ?? 'images/no-image.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php if (!empty($product['discount'])): ?>
                                <span class="discount-badge">-<?= $product['discount'] ?>%</span>
                            <?php endif; ?>
                        </div>

                        <!-- Информация о товаре -->
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            
                            <div class="product-meta">
                                <div class="availability <?= ($product['stock'] ?? 0) > 0 ? 'in-stock' : 'out-of-stock' ?>">
                                    <?= ($product['stock'] ?? 0) > 0 ? 'В наличии' : 'Нет в наличии' ?>
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
                                    <input type="number" class="quantity-input" value="1" min="1" max="<?= $product['stock'] ?? 0 ?>" data-id="<?= $product['id'] ?>">
                                    <button class="quantity-btn plus">+</button>
                                </div>
                                <button class="btn add-to-cart"
                                    data-id="<?= $product['id'] ?>"
                                    data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                                    data-price="<?= isset($product['discount']) && $product['discount'] > 0
                                        ? $product['price'] * (1 - $product['discount'] / 100)
                                        : $product['price'] ?>">
                                    В корзину
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>По запросу "<?= htmlspecialchars($searchQuery) ?>" ничего не найдено.</p>
                    <a href="catalog.php" class="btn">Перейти в каталог</a>
                </div>
            <?php endif; ?>
        </div>
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
