<?php
require_once 'config.php';
require_once 'functions.php';

$searchQuery = trim($_GET['q'] ?? '');
$products = [];

if (!empty($searchQuery)) {
    $products = searchProductsByName($searchQuery);

    // Если найден ровно один товар — редиректим на страницу товара
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
<script>
    .product-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: flex-start;
}

.product-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
    width: 240px;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.3s ease;
    cursor: default;
}

.product-card:hover {
    box-shadow: 0 6px 20px rgb(0 0 0 / 0.15);
}

.product-image {
    position: relative;
    width: 100%;
    padding-top: 75%; /* соотношение 4:3 */
    overflow: hidden;
    border-radius: 8px 8px 0 0;
    background-color: #f7f7f7;
}

.product-image img {
    position: absolute;
    top: 50%;
    left: 50%;
    max-width: 100%;
    max-height: 100%;
    transform: translate(-50%, -50%);
    object-fit: contain;
}

.discount-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background-color: #e74c3c;
    color: white;
    padding: 5px 10px;
    font-weight: 700;
    font-size: 0.85rem;
    border-radius: 4px;
    z-index: 2;
}

.product-info {
    padding: 12px 15px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.product-info h3 {
    margin: 0 0 10px 0;
    font-size: 1rem;
    line-height: 1.2;
    color: #222;
    min-height: 48px; /* фиксированная высота, чтобы карточки выравнивались */
}

.product-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 12px;
}

.availability.in-stock {
    color: #27ae60;
    font-weight: 600;
}

.availability.out-of-stock {
    color: #c0392b;
    font-weight: 600;
}

.rating {
    color: #f39c12;
    font-weight: 600;
}

.product-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #222;
}

.old-price {
    text-decoration: line-through;
    color: #999;
    margin-right: 8px;
}

.current-price {
    color: #27ae60;
}

.product-actions {
    padding: 10px 15px;
    border-top: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 10px;
    background-color: #fafafa;
}

.quantity-control {
    display: flex;
    border: 1px solid #ccc;
    border-radius: 4px;
    overflow: hidden;
}

.quantity-btn {
    background-color: #eee;
    border: none;
    cursor: pointer;
    padding: 6px 12px;
    font-size: 1.1rem;
    user-select: none;
    transition: background-color 0.3s ease;
}

.quantity-btn:hover {
    background-color: #ddd;
}

.quantity-input {
    width: 45px;
    text-align: center;
    border: none;
    outline: none;
    font-size: 1rem;
    padding: 6px 0;
}

.btn.add-to-cart {
    flex-grow: 1;
    background-color: #27ae60;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-weight: 700;
    cursor: pointer;
    padding: 10px 15px;
    transition: background-color 0.3s ease;
}

.btn.add-to-cart:hover {
    background-color: #219150;
}

</script>
