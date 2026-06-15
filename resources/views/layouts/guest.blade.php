<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', '補習班管理系統') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10 bg-gradient-to-b from-[#eff5f1] to-[#e2efe8]">
            {{-- 品牌（與後台一致）--}}
            <a href="/" class="flex items-center gap-3 mb-7">
                <span class="grid place-items-center h-12 w-12 rounded-xl bg-emerald-600 text-white font-bold text-2xl shadow-sm">補</span>
                <div>
                    <div class="text-xl font-bold text-gray-800 leading-tight">補習班行政管理系統</div>
                    <div class="text-xs text-gray-500 mt-0.5">後台管理登入</div>
                </div>
            </a>

            <div class="w-full sm:max-w-md px-6 py-7 bg-white rounded-2xl border border-gray-100 shadow-xl shadow-emerald-900/5">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-gray-400">© {{ date('Y') }} 補習班行政管理系統 · 作品集 Demo</p>
        </div>
    </body>
</html>
