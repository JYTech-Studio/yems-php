# 部署指南（Supabase + Zeabur）

把這個 Laravel 系統上線的完整步驟。本地用 SQLite，上線用 Supabase 的 PostgreSQL，平台用 Zeabur（Railway 步驟幾乎相同）。

---

## 0. 推上 GitHub（一次性）

專案目前還不是 git repo。在專案根目錄：

```bash
git init
git add .
git commit -m "init: 補習班管理系統 Laravel 版"
git branch -M main
git remote add origin git@github.com:JYTech-Studio/yems-laravel.git   # repo 先在 GitHub 建好
git push -u origin main
```

> `.gitignore` 已擋掉 `.env`、`database/*.sqlite`、上傳照片、`vendor`、`node_modules`，不會外洩。

---

## 1. 建 Supabase 資料庫

1. https://supabase.com 建一個新專案（免費方案即可），記住你設定的 **Database Password**。
2. 進 **Project Settings → Database → Connection string**，選 **Transaction pooler**（port 6543），抄下：
   - Host：`aws-0-xxx.pooler.supabase.com`
   - Port：`6543`
   - Database：`postgres`
   - User：`postgres.xxxxxxxx`

---

## 2. 在 Zeabur 部署

1. https://zeabur.com 用 GitHub 登入，建 Project → Add Service → 選你的 `yems-laravel` repo。
   Zeabur 會自動辨識為 PHP / Laravel。
2. 到該 service 的 **Variables**，貼上以下環境變數（值換成你的）：

   ```
   APP_NAME=補習班管理系統
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=                      # 見下方第 3 步產生
   APP_URL=https://你的網址.zeabur.app

   DB_CONNECTION=pgsql
   DB_HOST=aws-0-xxx.pooler.supabase.com
   DB_PORT=6543
   DB_DATABASE=postgres
   DB_USERNAME=postgres.xxxxxxxx
   DB_PASSWORD=你的Supabase密碼

   SESSION_DRIVER=database
   CACHE_STORE=database
   QUEUE_CONNECTION=database
   FILESYSTEM_DISK=public        # 先用 public；要存到 Supabase Storage 再改 s3（見第 5 步）
   LOG_CHANNEL=stack
   ```

3. **產生 APP_KEY**：本地跑 `php artisan key:generate --show`，把印出的 `base64:...` 整串貼到 Zeabur 的 `APP_KEY`。

---

## 3. 上線後初始化資料庫（第一次部署後跑一次）

在 Zeabur 該 service 的 **Terminal / Console**（或設成 build 後指令）執行：

```bash
php artisan migrate --force --seed     # 建表 + 灌入 demo 帳號與假資料
php artisan storage:link               # 讓上傳照片可公開存取
php artisan config:cache
php artisan route:cache
```

> `--seed` 會建立 demo 帳號（`admin@demo.com` / `Demo1234` 等）。
> 正式給客戶看時，記得改密碼或重新灌乾淨資料。

---

## 4. 確認

打開 `https://你的網址.zeabur.app` →

- 應自動導向登入頁
- 用 `admin@demo.com` / `Demo1234` 登入 → 看到 Dashboard
- 把這個網址 + 帳密填進求職表單即可

---

## 5.（選配）照片改存 Supabase Storage

預設 `FILESYSTEM_DISK=public` 會把照片存在容器本機，**重新部署會消失**。要永久保存：

1. Supabase → **Storage** 建一個 public bucket（例如 `uploads`）。
2. Supabase → **Project Settings → Storage** 取得 S3 相容憑證。
3. 在 Zeabur Variables 補：

   ```
   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=...
   AWS_SECRET_ACCESS_KEY=...
   AWS_DEFAULT_REGION=ap-northeast-1
   AWS_BUCKET=uploads
   AWS_USE_PATH_STYLE_ENDPOINT=true
   AWS_ENDPOINT=https://xxxxxxxx.supabase.co/storage/v1/s3
   ```

程式碼不用改——照片儲存本來就是讀 `config('filesystems.default')` 動態切換的。

---

## 常見問題

| 症狀 | 處理 |
|------|------|
| 開站 500、白畫面 | `APP_KEY` 沒設或格式錯；`APP_DEBUG=true` 暫時打開看錯誤 |
| `could not find driver` | 平台缺 `pdo_pgsql`；Zeabur 的 PHP 預設有，Railway 用 nixpacks 需確認 |
| 登入後一直跳回登入頁 | `SESSION_DRIVER=database` 但 sessions 表沒 migrate；確認第 3 步有跑 |
| 照片上傳後看不到 | 沒跑 `php artisan storage:link`，或該用第 5 步的 S3 |
