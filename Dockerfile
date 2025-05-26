# Используем официальный PHP-образ с Apache
FROM php:8.2-apache

# Устанавливаем необходимые расширения
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql


# Копируем файлы проекта в папку сайта
COPY . /var/www/html/

# Настраиваем рабочую директорию как online-shop
WORKDIR /var/www/html/online-shop

# Меняем корень сайта Apache на папку online-shop
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/online-shop|' /etc/apache2/sites-available/000-default.conf

# Назначаем владельца и права
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Включаем mod_rewrite
RUN a2enmod rewrite

# Устанавливаем временную зону
ENV TZ=Europe/Moscow
