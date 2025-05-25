# Используем официальный PHP-образ с Apache
FROM php:8.2-apache

# Устанавливаем необходимые расширения
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Включаем mod_rewrite для ЧПУ (если нужно)
RUN a2enmod rewrite

# Копируем файлы сайта в папку веб-сервера
COPY . /var/www/html/

# Устанавливаем права
RUN chown -R www-data:www-data /var/www/html
