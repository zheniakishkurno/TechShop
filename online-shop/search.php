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
<style>
.search-results-page {
    padding: 20px 0 40px;
}

.search-title {
    font-size: 24px;
    margin: 20px 0 30px;
    color: #333;
    font-weight: 600;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.product-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
    border: 1px solid #eee;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.product-image-container {
    position: relative;
    width: 100%;
    padding-top: 100%; /* Квадратное соотношение */
    background: #f9f9f9;
    overflow: hidden;
}

.product-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 15px;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.discount-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #ff3d00, #ff6d00);
    color: #fff;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 13px;
    z-index: 2;
}

.product-info {
    padding: 18px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.product-name {
    font-size: 16px;
    margin: 0 0 12px;
    line-height: 1.4;
    color: #333;
    font-weight: 500;
    flex-grow: 1;
}

.product-meta {
    margin-bottom: 15px;
}

.availability {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 10px;
}

.availability.in-stock {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.availability.out-of-stock {
    background-color: #ffebee;
    color: #c62828;
}

.rating {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
}

.stars {
    color: #ffc107;
    font-size: 14px;
    letter-spacing: 1px;
}

.reviews {
    font-size: 12px;
    color: #757575;
    margin-left: 5px;
}

.product-price {
    margin: 10px 0 15px;
}

.old-price {
    color: #9e9e9e;
    text-decoration: line-through;
    font-size: 14px;
    margin-right: 8px;
}

.current-price {
    font-weight: 700;
    color: #333;
    font-size: 20px;
}

.product-actions {
    display: flex;
    gap: 10px;
    margin-top: auto;
}

.quantity-control {
    display: flex;
    align-items: center;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    overflow: hidden;
    width: 100px;
}

.quantity-btn {
    background: #f5f5f5;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    font-size: 16px;
    color: #555;
    transition: background 0.2s;
}

.quantity-btn:hover {
    background: #e0e0e0;
}

.quantity-input {
    width: 40px;
    border: none;
    text-align: center;
    font-size: 14px;
    -moz-appearance: textfield;
}

.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.btn.add-to-cart {
    flex-grow: 1;
    background: linear-gradient(135deg, #1976d2, #2196f3);
    color: white;
    border: none;
    padding: 10px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn.add-to-cart:hover {
    background: linear-gradient(135deg, #1565c0, #1976d2);
    box-shadow: 0 3px 10px rgba(25, 118, 210, 0.3);
}

.no-results {
    text-align: center;
    padding: 50px 20px;
    background: #f9f9f9;
    border-radius: 10px;
    margin: 30px 0;
}

.no-results p {
    font-size: 18px;
    color: #555;
    margin-bottom: 20px;
}

.no-results .btn {
    background: #1976d2;
    color: white;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: background 0.3s;
}

.no-results .btn:hover {
    background: #1565c0;
}

@media (max-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 15px;
    }
    
    .product-info {
        padding: 15px;
    }
    
    .product-actions {
        flex-direction: column;
    }
    
    .quantity-control {
        width: 100%;
    }
}
</style>
