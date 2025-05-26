FROM php:8.2-apache

# Устанавливаем PostgreSQL драйверы
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Копируем файлы проекта
COPY . /var/www/html/

# Назначаем рабочую папку
WORKDIR /var/www/html/online-shop

# Обновляем корень сайта Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/online-shop|' /etc/apache2/sites-available/000-default.conf

# Назначаем права
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Включаем mod_rewrite
RUN a2enmod rewrite

# Устанавливаем временную зону
ENV TZ=Europe/Moscow
