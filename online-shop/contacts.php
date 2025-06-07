<?php require_once 'header.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TechShop - лучший магазин электроники с огромным выбором гаджетов по доступным ценам.">
    <title><?= isset($page_title) ? "$page_title | " : "" ?>TechShop</title>
    
    <link rel="icon" href="/online-shop/favicon.ico" type="image/x-icon" />

    <!-- Подключение основного CSS файла -->
    <link rel="stylesheet" href="css/style.css"> <!-- Убедитесь, что путь правильный -->
</head>
<div class="contacts-page">
    <div class="container">
        <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="index.php">Главная</a> / 
            <span>Контакты</span>
        </div>

        <h1>Контактная информация</h1>

        <section class="contact-info">
            <h2>Наши контактные данные</h2>
            <p>Мы всегда готовы ответить на ваши вопросы. Свяжитесь с нами удобным для вас способом:</p>
            <ul>
                <li><strong>Телефон:</strong> +7 800 555 35 35</li>
                <li><strong>Email:</strong> support@techshop.ru</li>
                <li><strong>Адрес:</strong> Минск, ул. Уручье, 4</li>
            </ul>
        </section>
        <section class="map">
            <h2>Наша локация</h2>
            <!-- Вставляем карту -->
            <div class="map-container">
<iframe
  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2350.0364765442616!2d27.690102!3d53.954987!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46dbc939d590d88f%3A0x4e4ba1bcc6ff808f!2z0JzQs9C40YLRg9GA0LDQutCw0YDRg9C90YvQuSDQn9GA0LDRgdC90LjQutC-0LPQviDQnNC-0YHRgtCw0L3QsNGPINGD0LsuINCc0L7RgdC60LLQsA!5e0!3m2!1sru!2sby!4v1716201234567!5m2!1sru!2sby"
  width="100%"
  height="450"
  style="border:0;"
  allowfullscreen=""
  loading="lazy"
  referrerpolicy="no-referrer-when-downgrade">
</iframe>
        </div>
        </section>
    </div>
</div>

<?php require_once 'footer.php'; ?>
