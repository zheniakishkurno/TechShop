<?php
require_once 'functions.php';

$product_id = $_GET['id'] ?? 0;
$product = getProductById($product_id);

if (!$product) {
    header('Location: catalog.php');
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
        // Добавляем отзыв без проверки на существующий
        $stmt = $pdo->prepare("
            INSERT INTO reviews (product_id, user_id, rating, comment) 
            VALUES (?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$product_id, $_SESSION['user_id'], $rating, $comment])) {
            $review_success = 'Спасибо за ваш отзыв!';
        } else {
            $review_error = 'Произошла ошибка при добавлении отзыва';
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
    
   <link rel="icon" href="/favicon.ico" type="image/x-icon" />

    <meta name="description" content="TechShop - лучший магазин электроники с огромным выбором гаджетов по доступным ценам.">
    <title><?= isset($page_title) ? "$page_title | " : "" ?>TechShop</title>
    
    <!-- Подключение CSS файла -->
    <link rel="stylesheet" href="css/style.css"> <!-- Укажите правильный путь к вашему файлу -->
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Стили для секции отзывов */
        .reviews-section {
            margin-top: 60px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 32px;
        }

        .reviews-header {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 40px;
            margin-bottom: 40px;
            padding-bottom: 32px;
            border-bottom: 1px solid #eee;
        }

        .reviews-summary {
            text-align: center;
            padding: 24px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .big-rating {
            font-size: 64px;
            font-weight: bold;
            color: #333;
            line-height: 1;
            margin-bottom: 8px;
        }

        .rating-stars {
            color: #FFB800;
            font-size: 28px;
            margin-bottom: 12px;
        }

        .total-reviews {
            color: #666;
            font-size: 15px;
        }

        /* Форма отзыва */
        .review-form {
            background: #f8f9fa;
            padding: 24px;
            border-radius: 12px;
        }

        .review-form h3 {
            margin: 0 0 20px 0;
            font-size: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 8px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 32px;
            color: #ddd;
            transition: color 0.2s;
        }

        .star-rating label:before {
            content: '★';
        }

        .star-rating input:checked ~ label,
        .star-rating:hover label:hover,
        .star-rating:hover label:hover ~ label {
            color: #FFB800;
        }

        .form-group textarea {
            width: 100%;
            padding: 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: vertical;
            min-height: 120px;
            font-size: 15px;
            transition: border-color 0.2s;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #0d6efd;
        }

        .submit-review {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .submit-review:hover {
            background: #0b5ed7;
        }

        /* Список отзывов */
        .reviews-list {
            display: grid;
            gap: 24px;
        }

        .review {
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #eee;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .review:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .reviewer-name {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }

        .review-date {
            color: #888;
            font-size: 14px;
        }

        .review-rating {
            color: #FFB800;
            font-size: 20px;
            margin: 12px 0;
        }

        .review-text {
            color: #444;
            line-height: 1.6;
            font-size: 15px;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        /* Адаптивность для мобильных устройств */
        @media (max-width: 768px) {
            .reviews-header {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .reviews-summary {
                padding: 20px;
            }

            .big-rating {
                font-size: 48px;
            }

            .rating-stars {
                font-size: 24px;
            }

            .review {
                padding: 20px;
            }
        }

        .add-to-cart-button {
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 500;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .add-to-cart-button:hover:not(.disabled) {
            background-color: #0b5ed7;
        }

        .add-to-cart-button.disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>

<div class="product-page">
    <div class="container">
        <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="index.php">Главная</a> / 
            <a href="index.php#products">Каталог</a>/ 
            <span><?= htmlspecialchars($product['name']) ?></span>
        </div>
        
        <!-- Подробности товара -->
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
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= $avg_rating ? '★' : '☆' ?>
                            <?php endfor; ?>
                        </div>
                        <a href="#reviews" class="reviews"><?= count($reviews) ?> отзывов</a>
                    </div>
                    
                    <div class="availability <?= $product['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                        <?= $product['stock'] > 0 ? 'В наличии' : 'Нет в наличии' ?>
                    </div>
                </div>
                
                <div class="product-price">
                    <?php if ($product['discount'] > 0): ?>
                        <span class="old-price"><?= number_format($product['price'], 2, '.', ' ') ?> BYN</span>
                        <span class="current-price"><?= number_format($product['price'] * (1 - $product['discount']/100), 2, '.', ' ') ?> BYN</span>
                        <span class="discount">-<?= $product['discount'] ?>%</span>
                    <?php else: ?>
                        <span class="current-price"><?= number_format($product['price'], 2, '.', ' ') ?> BYN</span>
                    <?php endif; ?>
                </div>
                
                <!-- Количество товара -->
                <div class="product-actions">
                    <div class="quantity">
                        <button class="quantity-btn minus">-</button>
                        <input type="number" value="1" min="1" max="<?= $product['stock'] ?>" id="quantity-input">
                        <button class="quantity-btn plus">+</button>
                    </div>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <button class="add-to-cart-button" onclick="addToCart(<?= $product['id'] ?>)" data-id="<?= $product['id'] ?>">
                            В корзину
                        </button>
                    <?php else: ?>
                        <button class="add-to-cart-button disabled" disabled>
                            Нет в наличии
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="product-description">
                    <h3>Описание</h3>
                    <p><?= nl2br(htmlspecialchars((string)($product['description'] ?? ''))) ?></p>
                </div>
            </div>
        </div>

        <!-- Отзывы -->
        <div class="reviews-section" id="reviews">
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

<!-- Подключение JavaScript файла -->
<script src="js/product.js"></script> <!-- Укажите правильный путь к вашему файлу JS -->

<?php require_once 'footer.php'; ?> 
