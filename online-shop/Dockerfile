FROM php:8.2-apache

# Установка расширений PHP, необходимых для работы с MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Включаем mod_rewrite (если нужно .htaccess)
RUN a2enmod rewrite

# Копируем все файлы в директорию веб-сервера
COPY . /var/www/html/

# Настраиваем права доступа
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
