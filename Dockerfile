# ============================================================
# 補習班管理系統（Laravel）— Render 部署用 Dockerfile
# 多階段：① Node 編譯前端資產 → ② PHP 8.3 + Apache 跑站
# ============================================================

# ---------- ① 前端資產（Vite + Tailwind v4）----------
FROM node:20-alpine AS assets
WORKDIR /app
# 先裝相依（利用快取），再 copy 全部原始碼讓 Tailwind v4 能掃到 blade 模板
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build          # 產出 public/build

# ---------- ② PHP 應用 ----------
FROM php:8.3-apache

# 系統相依：pgsql / zip(phpspreadsheet) / gd(影像) / mbstring
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev \
        unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql mbstring zip gd bcmath \
    && rm -rf /var/lib/apt/lists/*

# Apache：DocumentRoot 指到 Laravel 的 public/，並開啟 rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN a2enmod rewrite \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's!<Directory /var/www/>!<Directory ${APACHE_DOCUMENT_ROOT}/>!g' /etc/apache2/apache2.conf \
    && printf '<Directory ${APACHE_DOCUMENT_ROOT}>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
        > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Composer（從官方 composer image 直接拿執行檔）
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 先 copy composer 檔安裝相依（利用 Docker 快取層）
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Copy 應用程式碼 + 從 assets 階段拿編好的前端資產
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker/start.sh

# 容器啟動：跑 migration + seed（idempotent）+ 快取，再起 Apache
CMD ["/var/www/html/docker/start.sh"]
