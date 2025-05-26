# Используем официальный образ PHP с Apache
FROM php:8.2-apache

# Устанавливаем необходимые зависимости
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql gd zip

# Включаем модули Apache
RUN a2enmod rewrite headers

# Копируем файлы приложения
COPY . /var/www/html/

# Устанавливаем права
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod 777 /var/www/html/uploads

# Настраиваем PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    echo "upload_max_filesize = 10M" >> "$PHP_INI_DIR/php.ini" && \
    echo "post_max_size = 10M" >> "$PHP_INI_DIR/php.ini" && \
    echo "memory_limit = 256M" >> "$PHP_INI_DIR/php.ini"

# Открываем порт 80
EXPOSE 80

# Команда запуска
CMD ["apache2-foreground"]
