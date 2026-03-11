# docker/php/Dockerfile
FROM php:8.1-fpm-alpine

# Аргументы для ID пользователя и группы, которые будут переданы из docker-compose
ARG UID
ARG GID

# Устанавливаем рабочую директорию
WORKDIR /app

# Создаем группу и пользователя с нужными ID, чтобы избежать проблем с правами на хост-машине.
RUN addgroup -g $GID -S appgroup && adduser -u $UID -S appuser -G appgroup

# Устанавливаем системные зависимости, необходимые для расширений PHP
RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    git \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev

# Устанавливаем расширения PHP, которые часто требуются для Yii2
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    opcache \
    gd

# Устанавливаем Composer, копируя его из официального образа
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Изменяем конфигурацию пула FPM, чтобы он запускал воркеры от имени нашего нового пользователя
RUN sed -i "s/user = www-data/user = appuser/g" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i "s/group = www-data/group = appgroup/g" /usr/local/etc/php-fpm.d/www.conf
