# Используем официальный PHP-образ с Apache
FROM php:8.2-apache

# Устанавливаем необходимые расширения для PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Копируем файлы в контейнер
COPY . /var/www/html/

# Настройки прав и доступа
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Включаем mod_rewrite для .htaccess
RUN a2enmod rewrite

# Установка timezone
ENV TZ=Europe/Moscow
