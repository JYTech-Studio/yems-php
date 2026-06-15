@props(['token' => null, 'back' => null])
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>家長專區 · {{ config('app.name', '補習班管理系統') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#f0f7f4] min-h-screen">
    <div class="max-w-md mx-auto min-h-screen bg-white shadow-sm">
        {{-- brand bar --}}
        <div class="bg-emerald-600 text-white px-5 py-4 flex items-center gap-3">
            <span class="grid place-items-center h-9 w-9 rounded-full bg-white/20 font-bold">補</span>
            <div class="leading-tight">
                <div class="font-bold">家長專區</div>
                <div class="text-[11px] text-emerald-100">補習班行政系統</div>
            </div>
        </div>

        @if ($back)
            <a href="{{ $back }}" class="inline-flex items-center gap-1 text-sm text-emerald-700 px-5 py-3 hover:underline">← 回家長專區</a>
        @endif

        @if (session('status'))
            <div class="mx-5 mt-3 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2.5 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mx-5 mt-3 rounded-lg bg-red-50 border border-red-200 px-4 py-2.5 text-sm text-red-700">
                @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <div class="px-5 py-4">{{ $slot }}</div>

        <div class="px-5 py-6 text-center text-[11px] text-gray-300">YEMS 家長 Portal</div>
    </div>
</body>
</html>
