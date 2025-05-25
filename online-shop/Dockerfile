FROM php:8.1-apache

# Включаем модуль rewrite для удобства (если нужен)
RUN a2enmod rewrite

# Копируем все файлы вашего сайта в корень веб-сервера
COPY . /var/www/html/

# Настраиваем права (если нужно)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
