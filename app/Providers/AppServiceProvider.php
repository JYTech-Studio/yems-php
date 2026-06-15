<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 部署在 Render/Heroku 這類平台時，TLS 在反向代理層終結、轉給容器的是 http，
        // 會導致產生的資產連結變 http（在 https 頁面被當 mixed content 擋掉）。
        // production 一律強制用 https 產生連結。
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
