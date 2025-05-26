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

# Включаем модуль Apache rewrite
RUN a2enmod rewrite

# Копируем файлы приложения в контейнер
COPY . /var/www/html/

# Устанавливаем права на файлы
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Настраиваем виртуальный хост Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Открываем порт 80
EXPOSE 80

# Команда запуска Apache
CMD ["apache2-foreground"]
