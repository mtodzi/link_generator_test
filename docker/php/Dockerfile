FROM yiisoftware/yii2-php:8.1-fpm

# Образ yii2-php уже содержит много нужных расширений, включая pdo_mysql.
# Если нужны дополнительные, их можно установить здесь. Например:
# RUN install-php-extensions intl soap

# Устанавливаем composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /app

# Копируем composer файлы и устанавливаем зависимости.
# Это кэширует зависимости, если файлы composer.* не меняются.
COPY app/composer.json app/composer.lock ./
RUN composer install --no-interaction --no-plugins --no-scripts --no-dev --optimize-autoloader

# Копируем остальной код приложения
COPY app/ .