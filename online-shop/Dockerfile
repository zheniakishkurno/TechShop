FROM php:8.1-apache

# Копируем все файлы в корень веб-сервера
COPY . /var/www/html/

# Включаем модуль переписывания URL, если нужен
RUN a2enmod rewrite

# Настройка прав (если нужно)
RUN chown -R www-data:www-data /var/www/html

# Экспонируем порт 80
EXPOSE 80

CMD ["apache2-foreground"]
