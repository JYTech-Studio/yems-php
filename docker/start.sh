#!/usr/bin/env sh
# 容器啟動腳本：設定埠號 → 建表/灌資料 → 快取設定 → 啟動 Apache
set -e

# Render 會用 $PORT 告訴我們要監聽哪個埠（本地預設 80）
PORT="${PORT:-80}"
sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# SQLite：確保資料庫檔存在且 Apache(www-data) 可讀寫（容器檔案系統是暫時的，重啟會重新建）
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    mkdir -p "$(dirname "$DB_FILE")"
    touch "$DB_FILE"
    chown -R www-data:www-data "$(dirname "$DB_FILE")"
fi

# 資料庫：建表（--force 才能在 production 跑），再灌 demo 資料（Seeder 內有防呆，灌過就跳過）
php artisan migrate --force
php artisan db:seed --force

# 上傳照片的公開連結
php artisan storage:link || true

# Production 效能快取
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 前景執行 Apache（PID 1）
exec apache2-foreground
