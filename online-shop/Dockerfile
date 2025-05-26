# Используем официальный PHP-образ с Apache
FROM php:8.2-apache

# Устанавливаем расширения для PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Копируем файлы
COPY . /var/www/html/

# Устанавливаем права
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Включаем mod_rewrite
RUN a2enmod rewrite

# Устанавливаем таймзону
ENV TZ=Europe/Moscow
