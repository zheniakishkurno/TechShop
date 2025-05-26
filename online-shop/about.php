<?php
$page_title = "О сайте";
require_once 'header.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TechShop - лучший магазин электроники с огромным выбором гаджетов по доступным ценам.">
    <title><?= isset($page_title) ? "$page_title | " : "" ?>TechShop</title>
    
    <!-- Подключение основного CSS файла -->
    <link rel="stylesheet" href="/css/style.css">
</head>


<div class="about-page">
    <div class="container">
        <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="index.php">Главная</a> / 
            <span>О сайте</span>
        </div>

        <h1>О нашем магазине TechShop</h1>

        <section class="about-info">
            <h2>Добро пожаловать в TechShop!</h2>
           <p><span class="blue-text">TechShop</span> — это ваш надежный онлайн-магазин, где вы найдете самый широкий ассортимент электроники, гаджетов и аксессуаров от известных брендов по доступным ценам.</p>
             <p><span class="blue-text">Наша цель</span> — предоставить вам качественные товары, которые улучшат вашу жизнь, а также сделать покупки простыми и удобными. Мы заботимся о наших клиентах, и поэтому предлагаем только проверенную продукцию с гарантией качества.</p>
        </section>

        <section class="mission">
            <h2>Наша миссия</h2>
             <p><span class="blue-text">Наша миссия</span> — быть лучшим онлайн-магазином, предлагающим новейшие технологии и инновационные решения для ваших повседневных нужд. Мы стремимся обеспечить вас высококачественной техникой и отличным сервисом, чтобы покупки в нашем магазине приносили вам только положительные эмоции.</p>
        </section>

        <section class="our-advantages">
            <h2>Наши преимущества</h2>
            <ul>
                <li><strong>Широкий ассортимент:</strong> Мы предлагаем товары от ведущих мировых брендов в области электроники и бытовой техники.</li>
                <li><strong>Гарантия качества:</strong> Все товары, представленные на нашем сайте, проходят строгий контроль качества и имеют официальную гарантию.</li>
                <li><strong>Быстрая доставка:</strong> Мы гарантируем быструю и безопасную доставку всех заказов по Минск.</li>
                <li><strong>Клиентская поддержка:</strong> Наша команда всегда готова помочь вам с выбором товара и ответить на любые вопросы.</li>
            </ul>
        </section>

    </div>
</div>

<!-- Подключение JavaScript файла -->
<script src="js/product.js"></script> <!-- Убедитесь, что путь правильный -->

<?php require_once 'footer.php'; ?>

<style>
    .about-page {
        background-color: #fff;
        padding: 40px 0;
    }

    .about-page h1 {
        font-size: 36px;
        color: #212727;
        text-align: center;
        margin-bottom: 20px;
    }

    .about-page section {
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }

    .about-page section h2 {
        font-size: 28px;
        color: #007bff; /* Темный цвет для заголовков */
        margin-bottom: 15px;
    }

    .about-page section p {
        font-size: 16px;
        line-height: 1.6;
        color: #555;
    }

    .about-page ul {
        list-style-type: none;
        padding-left: 0;
    }

    .about-page ul li {
        font-size: 16px;
        color: #555;
        margin-bottom: 10px;
    }

    .blue-text {
        color: #007bff; /* Синий цвет только для ключевых фраз */
        font-weight: 600;
    }

    .breadcrumbs {
        font-size: 16px;
        margin-bottom: 20px;
        color: #555;
    }

    .breadcrumbs a {
        color: #007bff;
        text-decoration: none;
    }

    .breadcrumbs span {
        color: #555;
    }
    </style>
