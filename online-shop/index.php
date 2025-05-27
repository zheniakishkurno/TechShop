<?php
require_once 'header.php';
require_once 'functions.php';

$category_id = $_GET['category_id'] ?? null;
$search_query = $_GET['q'] ?? null;
$sort = $_GET['sort'] ?? 'newest';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

$categories = getCategories();

$total_products = 0;

if ($category_id) {
    $products = getProducts($category_id, $per_page, $offset);
    $total_products = countProductsByCategory($category_id);
    $section_title = "Товары из выбранной категории";
} elseif ($search_query) {
    $products = searchProductsByName($search_query, $per_page, $offset);
    $total_products = countProductsBySearch($search_query);
    $section_title = "Результаты поиска: " . htmlspecialchars($search_query);
} else {
    $products = getProducts(null, $per_page, $offset);
    $total_products = countAllProducts();
    $section_title = "Все товары";
}

$total_pages = ceil($total_products / $per_page);

// Сортировка товаров (локально после получения с лимитом)
if ($sort === 'price_asc') {
    usort($products, fn($a, $b) => $a['price'] <=> $b['price']);
} elseif ($sort === 'price_desc') {
    usort($products, fn($a, $b) => $b['price'] <=> $a['price']);
} elseif ($sort === 'name_asc') {
    usort($products, fn($a, $b) => strcmp($a['name'], $b['name']));
} elseif ($sort === 'name_desc') {
    usort($products, fn($a, $b) => strcmp($b['name'], $a['name']));
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TechShop - лучший магазин электроники с огромным выбором гаджетов по доступным ценам.">
    <title>TechShop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php if (!$category_id): ?>
<section class="hero">
    <div class="container">
        <h1>Добро пожаловать в TechShop</h1>
        <p>Лучшие гаджеты и электроника по доступным ценам</p>
        <a href="#products" class="btn btn-primary">Смотреть товары</a>
    </div>
</section>
<?php endif; ?>

<section class="categories">
    <div class="container">
        <h2>Категории товаров</h2>
        <div class="categories-grid">
            <div class="category-card <?= !$category_id ? 'active' : '' ?>">
                <a href="index.php" class="category-link">
                    <h3 class="category-title">Все</h3>
                </a>
            </div>
            <?php foreach ($categories as $category): ?>
                <div class="category-card <?= $category_id == $category['id'] ? 'active' : '' ?>">
                    <a href="index.php?category_id=<?= $category['id'] ?>" class="category-link">
                        <img src="<?= htmlspecialchars(formatImagePath($category['image_url'])) ?>"
                             alt="<?= htmlspecialchars($category['name']) ?>" class="category-img">
                        <h3 class="category-title"><?= htmlspecialchars($category['name']) ?></h3>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="products" id="products">
    <div class="container">
        <div class="products-header">
            <h2><?= $section_title ?></h2>
            <div class="sort-options">
                <span>Сортировка:</span>
                <select id="sort-select" onchange="window.location.href=this.value">
                    <?php
                    $baseParams = $_GET;
                    unset($baseParams['sort']);
                    foreach ([
                        'newest' => 'Новинки',
                        'price_asc' => 'Цена по возрастанию',
                        'price_desc' => 'Цена по убыванию',
                        'name_asc' => 'Название А-Я',
                        'name_desc' => 'Название Я-А',
                    ] as $value => $label): ?>
                    <?php $params = array_merge($baseParams, ['sort' => $value]); ?>
                    <option value="?<?= http_build_query($params) ?>" <?= $sort === $value ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="products-grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <?php
                    $product_image = !empty($product['image_url']) ? formatImagePath($product['image_url']) : 'images/no-image.png';
                    $old_price = number_format($product['price'], 2, '.', ' ');
                    $new_price = $product['discount'] > 0
                        ? number_format($product['price'] * (1 - $product['discount'] / 100), 2, '.', ' ')
                        : $old_price;
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="product.php?id=<?= $product['id'] ?>">
                                <img src="<?= htmlspecialchars($product_image) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
                                <?php if ($product['discount'] > 0): ?>
                                    <span class="discount-badge">-<?= $product['discount'] ?>%</span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">
                                <a href="product.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                            </h3>
                            <div class="product-price">
                                <?php if ($product['discount'] > 0): ?>
                                    <span class="old-price"><?= $old_price ?> ₽</span>
                                    <span class="current-price"><?= $new_price ?> ₽</span>
                                <?php else: ?>
                                    <span class="current-price"><?= $old_price ?> ₽</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-outline">Подробнее</a>
                                <button class="btn btn-primary btn-buy-now" data-id="<?= $product['id'] ?>">Купить</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Нет товаров для отображения.</p>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                   class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
        <?php endif; ?>
    </div>
</section>

<script src="js/main.js"></script>
<?php require_once 'footer.php'; ?>
</body>
</html>

<script>
function buyNow(productId, quantity = 1) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: parseInt(productId), quantity: quantity })
    })
    .then(response => response.redirected ? window.location.href = response.url : response.json())
    .then(data => {
        if (data && data.success) window.location.href = 'cart.php';
        else alert(data?.error || 'Ошибка при добавлении в корзину');
    })
    .catch(error => {
        console.error('Ошибка при добавлении в корзину:', error);
        alert('Произошла ошибка');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-buy-now').forEach(button => {
        button.addEventListener('click', () => buyNow(button.dataset.id));
    });
});
</script>
