<?php
require_once 'functions.php';
$current_user = getCurrentUser();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? "$page_title | " : "" ?>TechShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/online-shop/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<header class="header">
    <div class="container">
        <div class="header-top">
            <div class="logo">
                <a href="index.php">Tech<span>Shop</span></a>
            </div>

            <div class="search-bar">
    <form action="search.php" method="get">
        <input type="text" name="q" placeholder="Найти товар..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>
</div>

 
            <div class="header-icons">
    <?php if ($current_user): ?>
        <a href="profile.php" class="icon user">
            <i class="fas fa-user"></i>
            <span><?= htmlspecialchars($current_user['first_name']) ?></span>
        </a>
    <?php else: ?>
        <a href="login.php" class="icon login">
            <i class="fas fa-sign-in-alt"></i>
            <span>Войти</span>
        </a> 
    <?php endif; ?>

    <a href="cart.php" class="icon cart">
        <i class="fas fa-shopping-cart"></i>
        <span id="cart-count">0</span>
    </a>
</div>

        </div>

        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Главная</a></li>
                <li><a href="index.php#products">Каталог</a></li>
                <li><a href="about.php">О нас</a></li>
                <li><a href="contacts.php">Контакты</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="admin.php">Админ-панель</a></li>
                <?php endif; ?>
            </ul>
        </nav> 
    </div>
</header>
<main class="main">