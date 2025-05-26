# Используем официальный PHP-образ с Apache
FROM php:8.2-apache

# Устанавливаем необходимые расширения
RUN docker-php-ext-install pdo pdo_mysql

# Копируем файлы в контейнер
COPY . /var/www/html/

# Настройки Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Включаем mod_rewrite
RUN a2enmod rewrite

# Установка timezone
ENV TZ=Europe/Moscow
