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
<script>
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', () => {
        const productId = button.dataset.id;
        const productName = button.dataset.name;
        const productPrice = parseFloat(button.dataset.price);
        const quantityInput = button.parentElement.querySelector('.quantity-input');
        const quantity = quantityInput ? parseInt(quantityInput.value) : 1;

        // Здесь можно отправить данные на сервер, добавить в localStorage, обновить UI и т.п.
        alert(`Добавлено в корзину:\n${productName}\nКоличество: ${quantity}\nЦена за единицу: ${productPrice} ₽`);
        
        // Пример: можно добавить AJAX запрос для добавления в корзину
    });
});
</script>

<?php require_once 'footer.php'; ?>
<script>
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.product-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-image {
    position: relative;
    width: 100%;
    padding-top: 100%;
    background: #f5f5f5;
    overflow: hidden;
}

.product-image img {
    position: absolute;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.discount-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #ff3d00;
    color: #fff;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: bold;
    font-size: 14px;
}

.product-info {
    padding: 15px;
}

.product-info h3 {
    font-size: 18px;
    margin: 0 0 8px;
    line-height: 1.3;
}

.product-meta {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.product-meta .availability.in-stock {
    color: #4caf50;
    font-weight: bold;
}

.product-meta .availability.out-of-stock {
    color: #f44336;
    font-weight: bold;
}

.product-price {
    margin: 10px 0;
    font-size: 16px;
}

.product-price .old-price {
    color: #888;
    text-decoration: line-through;
    margin-right: 8px;
}

.product-price .current-price {
    font-weight: bold;
    color: #000;
    font-size: 18px;
}

.product-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    padding-top: 10px;
    gap: 10px;
}

.quantity-control {
    display: flex;
    align-items: center;
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
    max-width: 100px;
}

.quantity-btn {
    background: #eee;
    border: none;
    padding: 6px 10px;
    cursor: pointer;
}

.quantity-input {
    width: 40px;
    border: none;
    text-align: center;
}

.btn.add-to-cart {
    flex-grow: 1;
    background: #1976d2;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s;
}

.btn.add-to-cart:hover {
    background: #125ea2;
}

</script>
