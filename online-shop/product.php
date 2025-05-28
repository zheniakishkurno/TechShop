<?php
require_once 'config.php';
require_once 'functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Получаем ID товара из URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: index.php');
    exit;
}

// Получаем информацию о товаре
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

// Обработка добавления отзыва
$review_error = '';
$review_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if ($rating < 1 || $rating > 5) {
        $review_error = 'Оценка должна быть от 1 до 5';
    } elseif (empty($comment)) {
        $review_error = 'Пожалуйста, напишите комментарий';
    } else {
        // Проверяем, не оставлял ли пользователь уже отзыв
        $check_stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
        $check_stmt->execute([$_SESSION['user_id'], $product_id]);
        
        if ($check_stmt->fetch()) {
            $review_error = 'Вы уже оставляли отзыв к этому товару';
        } else {
            // Добавляем отзыв
            $stmt = $pdo->prepare("
                INSERT INTO reviews (product_id, user_id, rating, comment) 
                VALUES (?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$product_id, $_SESSION['user_id'], $rating, $comment])) {
                // Обновляем количество отзывов у товара
                $update_stmt = $pdo->prepare("
                    UPDATE products 
                    SET reviews_count = reviews_count + 1 
                    WHERE id = ?
                ");
                $update_stmt->execute([$product_id]);
                
                $review_success = 'Спасибо за ваш отзыв!';
            } else {
                $review_error = 'Произошла ошибка при добавлении отзыва';
            }
        }
    }
}

// Получаем все отзывы к товару
$reviews_stmt = $pdo->prepare("
    SELECT r.*, u.first_name, u.last_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$reviews_stmt->execute([$product_id]);
$reviews = $reviews_stmt->fetchAll();

// Считаем средний рейтинг
$avg_rating = 0;
if (count($reviews) > 0) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $avg_rating = round($total_rating / count($reviews), 1);
}

// Увеличиваем счетчик просмотров
incrementProductViews($product_id);

$page_title = htmlspecialchars($product['name']);
 
require_once 'header.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TechShop - лучший магазин электроники с огромным выбором гаджетов по доступным ценам.">
    <title><?= isset($page_title) ? "$page_title | " : "" ?>TechShop</title>
    
    <!-- Подключение CSS файла -->
    <link rel="stylesheet" href="css/style.css"> <!-- Укажите правильный путь к вашему файлу -->
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .product-details {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .breadcrumbs {
            margin-bottom: 20px;
            color: #666;
        }

        .breadcrumbs a {
            color: #0d6efd;
            text-decoration: none;
        }

        .product-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 50px;
        }

        .product-gallery {
            background: #fff;
        }

        .product-image {
            width: 100%;
            height: auto;
            display: block;
        }

        .product-info {
            padding: 20px 0;
        }

        .product-info h1 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #333;
        }

        .product-meta {
            margin-bottom: 20px;
        }

        .rating-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .stars {
            color: #ffd700;
            font-size: 18px;
        }

        .rating-count {
            color: #666;
            font-size: 14px;
        }

        .availability {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
        }

        .in-stock {
            background: #d4edda;
            color: #155724;
        }

        .out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }

        .product-price {
            margin: 20px 0;
        }

        .current-price {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .old-price {
            color: #999;
            text-decoration: line-through;
            margin-right: 10px;
        }

        .discount {
            background: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 14px;
            margin-left: 10px;
        }

        .quantity {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            border: 1px solid #ddd;
            background: #fff;
            font-size: 18px;
            cursor: pointer;
        }

        #quantity {
            width: 50px;
            height: 35px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .add-to-cart {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .add-to-cart:hover {
            background: #0056b3;
        }

        .product-description {
            margin-top: 30px;
        }

        .product-description h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }

        .reviews-section {
            margin-top: 50px;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .reviews-header {
            display: flex;
            align-items: flex-start;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .reviews-summary {
            flex: 0 0 250px;
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .big-rating {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .rating-stars {
            font-size: 24px;
            color: #ffd700;
            margin-bottom: 10px;
        }

        .total-reviews {
            color: #666;
            font-size: 14px;
        }

        .review-form {
            flex: 1;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .review-form h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 5px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 24px;
            color: #ddd;
        }

        .star-rating label:before {
            content: '★';
        }

        .star-rating input:checked ~ label,
        .star-rating:hover label:hover,
        .star-rating:hover label:hover ~ label {
            color: #ffd700;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            resize: vertical;
            min-height: 100px;
        }

        .submit-review {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        .submit-review:hover {
            background: #0056b3;
        }

        .reviews-list {
            display: grid;
            gap: 20px;
        }

        .review {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .reviewer-name {
            font-weight: bold;
            color: #333;
        }

        .review-date {
            color: #888;
            font-size: 14px;
        }

        .review-rating {
            color: #ffd700;
            font-size: 18px;
            margin: 10px 0;
        }

        .review-text {
            color: #555;
            line-height: 1.6;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .product-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<div class="product-page">
    <div class="container">
        <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="index.php">Главная</a> / 
            <a href="catalog.php">Каталог</a> / 
            <span><?= htmlspecialchars($product['name']) ?></span>
        </div>
        
        <!-- Подробности товара -->
        <div class="product-details">
            <div class="product-container">
                <div class="product-gallery">
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                </div>
                
                <div class="product-info">
                    <h1><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <div class="product-meta">
                        <div class="rating-info">
                            <div class="stars">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $avg_rating ? '★' : '☆';
                                }
                                ?>
                            </div>
                            <span class="rating-count"><?= count($reviews) ?> отзывов</span>
                        </div>

                        <div class="availability <?= $product['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                            <?= $product['stock'] > 0 ? 'В наличии' : 'Нет в наличии' ?>
                        </div>
                    </div>
                    
                    <div class="product-price">
                        <?php if ($product['discount'] > 0): ?>
                            <span class="old-price"><?= number_format($product['price'], 2, '.', ' ') ?> ₽</span>
                            <span class="current-price"><?= number_format($product['price'] * (1 - $product['discount']/100), 2, '.', ' ') ?> ₽</span>
                            <span class="discount">-<?= $product['discount'] ?>%</span>
                        <?php else: ?>
                            <span class="current-price"><?= number_format($product['price'], 2, '.', ' ') ?> ₽</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="quantity">
                        <button type="button" class="quantity-btn minus">-</button>
                        <input type="number" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                        <button type="button" class="quantity-btn plus">+</button>
                    </div>

                    <button class="add-to-cart">В корзину</button>
                    
                    <div class="product-description">
                        <h3>Описание</h3>
                        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="reviews-section">
                <div class="reviews-header">
                    <div class="reviews-summary">
                        <div class="big-rating"><?= number_format($avg_rating, 1) ?></div>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= $avg_rating ? '★' : '☆' ?>
                            <?php endfor; ?>
                        </div>
                        <div class="total-reviews"><?= count($reviews) ?> отзывов</div>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="review-form">
                            <h3>Оставить отзыв</h3>
                            
                            <?php if ($review_error): ?>
                                <div class="alert alert-error"><?= htmlspecialchars($review_error) ?></div>
                            <?php endif; ?>
                            
                            <?php if ($review_success): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($review_success) ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="form-group">
                                    <label>Оценка:</label>
                                    <div class="star-rating">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                                            <label for="star<?= $i ?>" title="<?= $i ?> звезд"></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="comment">Комментарий:</label>
                                    <textarea id="comment" name="comment" required></textarea>
                                </div>

                                <button type="submit" class="submit-review">Отправить отзыв</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p>Чтобы оставить отзыв, пожалуйста, <a href="login.php">войдите</a> в свой аккаунт.</p>
                    <?php endif; ?>
                </div>

                <div class="reviews-list">
                    <?php if (empty($reviews)): ?>
                        <p>Пока нет отзывов. Будьте первым!</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review">
                                <div class="review-header">
                                    <span class="reviewer-name">
                                        <?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?>
                                    </span>
                                    <span class="review-date">
                                        <?= date('d.m.Y', strtotime($review['created_at'])) ?>
                                    </span>
                                </div>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?= $i <= $review['rating'] ? '★' : '☆' ?>
                                    <?php endfor; ?>
                                </div>
                                <div class="review-text">
                                    <?= htmlspecialchars($review['comment']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Подключение JavaScript файла -->
<script src="js/product.js"></script> <!-- Укажите правильный путь к вашему файлу JS -->

<?php require_once 'footer.php'; ?> 

