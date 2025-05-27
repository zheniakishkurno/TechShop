<?php
require_once 'header.php';
require_once 'functions.php';

$category_id = $_GET['category_id'] ?? null;
$search_query = $_GET['q'] ?? null;
$sort = $_GET['sort'] ?? 'newest';
$categories = getCategories();

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

$categories = getCategories();

// Получаем товары с учетом фильтров
// Получаем все товары, если нет фильтра категории или поиска
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



// Сортировка товаров
if ($sort === 'price_asc') {
    usort($products, function($a, $b) {
        return $a['price'] <=> $b['price'];
    });
} elseif ($sort === 'price_desc') { 
    usort($products, function($a, $b) {
        return $b['price'] <=> $a['price'];
    });
} elseif ($sort === 'name_asc') {
    usort($products, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
} elseif ($sort === 'name_desc') {
    usort($products, function($a, $b) {
        return strcmp($b['name'], $a['name']);
    });
}

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
function getProducts($category_id = null, $limit = 15, $offset = 0) {
    global $db;
    if ($category_id) {
        $stmt = $db->prepare("SELECT * FROM products WHERE category_id = ? LIMIT ? OFFSET ?");
        $stmt->execute([$category_id, $limit, $offset]);
    } else {
        $stmt = $db->prepare("SELECT * FROM products LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function searchProductsByName($query, $limit = 15, $offset = 0) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM products WHERE name LIKE ? LIMIT ? OFFSET ?");
    $stmt->execute(["%$query%", $limit, $offset]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countProductsByCategory($category_id) {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    return $stmt->fetchColumn();
}

function countProductsBySearch($query) {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE name LIKE ?");
    $stmt->execute(["%$query%"]);
    return $stmt->fetchColumn();
}

function countAllProducts() {
    global $db;
    $stmt = $db->query("SELECT COUNT(*) FROM products");
    return $stmt->fetchColumn();
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

<!-- Главная секция -->
<?php if (!$category_id): ?>
<section class="hero">
    <div class="container">
        <h1>Добро пожаловать в TechShop</h1>
        <p>Лучшие гаджеты и электроника по доступным ценам</p>
        <a href="#products" class="btn btn-primary">Смотреть товары</a>
    </div>
</section>
<?php endif; ?>

<!-- Категории -->
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

<!-- Товары -->
<section class="products" id="products">
    <div class="container">
        <div class="products-header">
            <h2><?= $section_title ?></h2>
            <div class="sort-options">
                <span>Сортировка:</span>
                <select id="sort-select" onchange="window.location.href=this.value">
                    <option value="?sort=newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Новинки</option>
                    <option value="?sort=price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Цена по возрастанию</option>
                    <option value="?sort=price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Цена по убыванию</option>
                    <option value="?sort=name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Название А-Я</option>
                    <option value="?sort=name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Название Я-А</option>
                </select>
            </div>
        </div>
        <div class="products-grid">
            <?php
            if (count($products) > 0):
                foreach ($products as $product):
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
                    <?php if ($total_pages > 1): ?>
    <nav class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
               class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
                <p>Нет товаров для отображения.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<script src="js/main.js"></script>

<?php require_once 'footer.php'; ?>
</body>
</html>
<script>
    // Добавить в корзину и сразу перейти в cart.php
    function buyNow(productId, quantity = 1) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: parseInt(productId),
                quantity: quantity
            })
        })
        .then(response => {
            if (response.redirected) {
                // Перенаправление с сервера (если это обычный POST)
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                // Для AJAX: переход в корзину вручную
                window.location.href = 'cart.php';
            } else if (data) {
                alert(data.error || 'Ошибка при добавлении в корзину');
            }
        })
        .catch(error => {
            console.error('Ошибка при добавлении в корзину:', error);
            alert('Произошла ошибка');
        });
    }

    // Обработка кликов по кнопкам "Купить"
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-buy-now').forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-id');
                buyNow(productId);
            });
        });
    });
</script>
