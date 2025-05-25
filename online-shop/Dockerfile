FROM php:8.1-apache

RUN docker-php-ext-install pdo pdo_mysql

# Включаем mod_rewrite (если нужен .htaccess)
RUN a2enmod rewrite

# Копируем все файлы в папку сайта
COPY . /var/www/html/

# Права
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
