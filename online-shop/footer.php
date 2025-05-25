<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechShop</title>

    <!-- Подключение CSS -->
    <link rel="stylesheet" href="/online-shop/css/style.css">

    <!-- Подключение иконок (если используете FontAwesome или другой набор иконок) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<!-- Контейнер для серого фона за футером -->
<div class="footer-container">
<footer class="footer">
    <div class="container">
        <div class="footer-grid">

            <!-- О магазине -->
            <div class="footer-col">
                <h3>TechShop</h3>
                <p>TechShop — современный интернет-магазин техники и гаджетов с доставкой по всему Минску. Только оригинальные товары и топ-сервис.</p>
            </div>

            <!-- Ссылки -->
            <div class="footer-col">
                <h4>Компания</h4>
                <ul>
                    <li><a href="about.php">О нас</a></li>
                    <li><a href="contacts.php">Контакты</a></li>
                </ul>
            </div>

            <!-- Контакты -->
            <div class="footer-col">
                <h4>Контакты</h4>
                <p><i class="fas fa-phone"></i> +375 (44) 123-45-67</p>
                <p><i class="fas fa-envelope"></i> support@gmail.com</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-vk"></i></a>
                    <a href="#"><i class="fab fa-telegram"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> TechShop. Все права защищены.</p>
        </div>
    </div>
</footer>
 
</div>

