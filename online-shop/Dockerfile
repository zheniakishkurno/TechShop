FROM php:8.1-apache

# Устанавливаем нужные расширения PHP (например, для работы с MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# Копируем все файлы сайта в директорию, обслуживаемую Apache
COPY . /var/www/html/

# Включаем mod_rewrite (если нужно для .htaccess)
RUN a2enmod rewrite

# Настраиваем права доступа (не обязательно, но полезно)
RUN chown -R www-data:www-data /var/www/html

# Объявляем порт (Render игнорирует EXPOSE, но это best practice)
EXPOSE 80
