<?php
require_once 'config.php';
require_once 'functions.php';

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];

// Если поисковый запрос пустой, показываем все товары
if (empty($searchQuery)) {
    $products = getProducts(); // Получаем все товары
} else {
    $products = searchProductsByName($searchQuery);

    // Если найден ровно один товар — редиректим на страницу товара
    if (count($products) === 1) {
        $productId = $products[0]['id'];
        header("Location: product.php?id=" . urlencode($productId));
        exit;
    }
}

$page_title = empty($searchQuery) ? 'Все товары' : 'Результаты поиска: ' . htmlspecialchars($searchQuery);
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
            <a href="index.php#products">Каталог</a>/ 
            <span>Поиск: "<?= htmlspecialchars($searchQuery) ?>"</span>
        </div>

        <!-- Результаты поиска -->
        <h1 class="search-title">Результаты поиска: "<?= htmlspecialchars($searchQuery) ?>"</h1>
        
        <?php if (!empty($products)): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $product['id'] ?>" class="product-link">
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
                            </div>
                        </a>
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

<script src="product.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработчики для кнопок количества
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            const max = parseInt(input.getAttribute('max')) || 999;
            let value = parseInt(input.value) || 1;

            if (this.classList.contains('minus') && value > 1) {
                input.value = value - 1;
            } else if (this.classList.contains('plus') && value < max) {
                input.value = value + 1;
            }
        });
    });

    // Обработчики для кнопок "В корзину"
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const quantityInput = this.closest('.product-actions').querySelector('.quantity-input');
            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
            
            // Отправка запроса на сервер
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Успешно добавлен товар
                    alert('Товар добавлен в корзину!');
                    
                    // Обновляем количество товаров в корзине на странице
                    const cartCountElement = document.getElementById('cart-count');
                    if (cartCountElement) {
                        cartCountElement.textContent = data.total_items;
                    }
                } else {
                    // Ошибка добавления товара
                    alert('Ошибка при добавлении товара в корзину: ' + (data.error || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при добавлении товара в корзину.');
            });
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>

<style>
.search-results-page {
    padding: 2rem 0;
}

.search-title {
    font-size: 1.75rem;
    margin-bottom: 2rem;
    color: var(--text);
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.product-card {
    background: var(--white);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
}

.product-link {
    text-decoration: none;
    color: inherit;
    flex: 1;
    display: block;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    cursor: pointer;
}

.product-image {
    position: relative;
    padding-top: 100%;
    overflow: hidden;
    background: var(--background);
}

.product-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 1rem;
}

.discount-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: var(--error);
    color: var(--white);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.product-info {
    padding: 1.25rem;
}

.product-info h3 {
    margin: 0 0 0.75rem;
    font-size: 1.1rem;
    color: var(--text);
    line-height: 1.4;
}

.product-meta {
    margin-bottom: 1rem;
}

.availability {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.in-stock {
    background-color: #dcfce7;
    color: var(--success);
}

.out-of-stock {
    background-color: #fee2e2;
    color: var(--error);
}

.rating {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.stars {
    color: #f59e0b;
    font-size: 0.9rem;
}

.reviews {
    color: var(--text-secondary);
    font-size: 0.8rem;
    margin-left: 0.5rem;
}

.product-price {
    margin-bottom: 1rem;
}

.old-price {
    text-decoration: line-through;
    color: var(--text-secondary);
    margin-right: 0.5rem;
    font-size: 0.9rem;
}

.current-price {
    font-weight: 600;
    color: var(--primary);
    font-size: 1.1rem;
}

.product-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
}

.quantity-control {
    display: flex;
    align-items: center;
    border: 1px solid var(--gray-light);
    border-radius: 6px;
    overflow: hidden;
    width: 100px;
}

.quantity-btn {
    background-color: #f1f5f9;
    width: 2rem;
    height: 2rem;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.2s;
    border: none;
}

.quantity-btn:hover {
    background-color: var(--gray-light);
}

.quantity-input {
    width: 3rem;
    height: 2rem;
    text-align: center;
    border: none;
    border-left: 1px solid var(--gray-light);
    border-right: 1px solid var(--gray-light);
    -moz-appearance: textfield;
}

.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.add-to-cart {
    flex-grow: 1;
    background-color: var(--primary);
    color: var(--white);
    height: 2rem;
    font-weight: 500;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.add-to-cart:hover {
    background-color: var(--primary-dark);
}

.no-results {
    text-align: center;
    padding: 3rem;
    background: var(--white);
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.no-results p {
    margin-bottom: 1.5rem;
    color: var(--text-secondary);
}

.no-results .btn {
    background-color: var(--primary);
    color: var(--white);
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
    transition: background-color 0.2s;
}

.no-results .btn:hover {
    background-color: var(--primary-dark);
}

@media (max-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }
    
    .product-actions {
        flex-direction: column;
    }
    
    .quantity-control {
        width: 100%;
    }
}
</style>
