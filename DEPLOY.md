# 部署指南

把這個 Laravel 系統上線。有兩條路：

- **推薦（最簡單）**：Render + SQLite — 零資料庫設定，免費，適合作品 demo。👉 看下面「方案 A」。
- **進階**：Render/Zeabur + Supabase PostgreSQL — 資料永久保存，較像正式環境。👉 看「方案 B」。

> 容器啟動腳本 `docker/start.sh` 會自動建表 + 灌 demo 資料，所以**部署後不用手動跑任何指令**。

---

## 方案 A：Render + SQLite（推薦）

資料庫用容器內建的 SQLite 檔，**不需要 Supabase、不需要任何資料庫帳密**。
缺點只有一個：容器休眠 / 重新部署後資料會**重置回乾淨的 demo 狀態**——對作品 demo 來說反而理想。

### 1. 先產生 APP_KEY

本地專案根目錄跑：

```bash
php artisan key:generate --show
```

複製印出的 `base64:....` 整串（待會貼進 Render）。

### 2. 在 Render 建服務

1. https://render.com 用 GitHub 登入 → **New → Web Service**
2. 連到你的 `JYTech-Studio/yems-php` repo
3. 設定：
   - **Language / Runtime**：選 **Docker**（Render 會自動讀專案根目錄的 `Dockerfile`）
   - **Instance Type**：選 **Free**
4. 展開 **Environment Variables**，加入這幾個：

   | Key | Value |
   |-----|-------|
   | `APP_KEY` | `base64:....`（第 1 步那串） |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_URL` | `https://yems-php.onrender.com`（建好後的網址，可先填、之後改） |
   | `DB_CONNECTION` | `sqlite` |
   | `SESSION_DRIVER` | `database` |
   | `CACHE_STORE` | `database` |
   | `FILESYSTEM_DISK` | `public` |

5. 按 **Create Web Service**。Render 開始用 Dockerfile build（第一次約 5～10 分鐘）。

### 3. 完成

build 成功後打開你的網址 `https://xxx.onrender.com`：

- 會自動導向登入頁
- 用 `admin@demo.com` / `Demo1234` 登入 → 看到 Dashboard
- 把這組「網址 + 帳密」填進求職表單即可

> 💤 免費方案閒置 15 分鐘會休眠，下次有人開**第一次載入慢約 30 秒**（喚醒中），之後就順暢。

---

## 方案 B：改用 Supabase（要永久保存資料時）

如果之後想讓資料不會重置，把資料庫換成 Supabase 的 PostgreSQL。**程式碼完全不用改，只改 Render 環境變數。**

1. https://supabase.com 建專案，記住 **Database Password**；Region 選 **Northeast Asia (Tokyo)**。
2. **Project Settings → Database → Connection string → Transaction pooler**（port 6543），抄下 Host / Port / Database / User。
3. 在 Render 環境變數把 `DB_CONNECTION` 改成 `pgsql`，並補上：

   | Key | Value |
   |-----|-------|
   | `DB_CONNECTION` | `pgsql` |
   | `DB_HOST` | `aws-0-xxx.pooler.supabase.com` |
   | `DB_PORT` | `6543` |
   | `DB_DATABASE` | `postgres` |
   | `DB_USERNAME` | `postgres.xxxxxxxx` |
   | `DB_PASSWORD` | 你的 Supabase 密碼 |

4. 存檔 → Render 自動重新部署，啟動腳本會在 Supabase 上建表 + 灌資料。

> 照片要永久保存的話，另把 `FILESYSTEM_DISK` 改 `s3` 並填 Supabase Storage 的 S3 憑證（`.env.example` 有欄位）。

---

## 常見問題

| 症狀 | 處理 |
|------|------|
| 開站 500、白畫面 | `APP_KEY` 沒設或格式錯；先把 `APP_DEBUG` 設 `true` 重新部署看錯誤訊息 |
| build 失敗 | 看 Render 的 **Logs**；多半是環境變數漏填 |
| 登入後一直跳回登入頁 | `SESSION_DRIVER`/`CACHE_STORE` 沒設成 `database` |
| 資料每次重置 | SQLite 的正常現象（方案 A）；要保存請走方案 B |
