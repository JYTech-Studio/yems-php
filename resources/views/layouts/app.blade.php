<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', '補習班管理') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-[#f3f6f4]">

            {{-- ===== Sidebar ===== --}}
            @php
                $nav = [
                    ['總覽', 'dashboard', 'dashboard', false],
                    ['學生', 'students.index', 'students.*', false],
                    ['家長', 'parents.index', 'parents.*', false],
                    ['課程', 'courses.index', 'courses.*', false],
                    ['點數帳戶', 'credits.index', 'credits.*', false],
                    ['點名工作檯', 'attendance.index', 'attendance.*', false],
                    ['聯絡簿', 'lesson-logs.index', 'lesson-logs.*', false],
                    ['請假管理', 'leave.index', 'leave.*', false],
                    ['報表', 'reports.index', 'reports.*', false],
                    ['帳號管理', 'accounts.index', 'accounts.*', true], // admin only
                ];
            @endphp

            <aside class="fixed inset-y-0 left-0 z-40 w-60 bg-white border-r border-gray-100 flex flex-col
                          transition-transform duration-200 lg:translate-x-0"
                   :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
                {{-- Brand --}}
                <div class="h-16 flex items-center gap-2.5 px-5 border-b border-gray-100">
                    <span class="grid place-items-center h-9 w-9 rounded-lg bg-emerald-600 text-white font-bold text-lg shadow-sm">補</span>
                    <div class="leading-tight">
                        <div class="font-bold text-gray-800">補習班</div>
                        <div class="text-[11px] text-gray-400">行政管理系統</div>
                    </div>
                </div>

                {{-- Nav --}}
                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                    @foreach ($nav as [$label, $routeName, $pattern, $adminOnly])
                        @continue($adminOnly && ! auth()->user()->isAdmin())
                        @if (Route::has($routeName))
                            @php($active = request()->routeIs($pattern))
                            <a href="{{ route($routeName) }}"
                               class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                                      {{ $active ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                @if ($active)<span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-full bg-emerald-600"></span>@endif
                                {{ $label }}
                            </a>
                        @else
                            <span class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 cursor-not-allowed select-none"
                                  title="建置中">{{ $label }}</span>
                        @endif
                    @endforeach
                </nav>

                <div class="px-4 py-3 border-t border-gray-100 text-[11px] text-gray-400">
                    YEMS · Laravel 版
                </div>
            </aside>

            {{-- backdrop (mobile) --}}
            <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
                 class="fixed inset-0 z-30 bg-black/30 lg:hidden"></div>

            {{-- ===== Main ===== --}}
            <div class="lg:pl-60">
                {{-- Topbar --}}
                <header class="sticky top-0 z-20 h-16 bg-white/90 backdrop-blur border-b border-gray-100 flex items-center justify-between px-4 sm:px-6">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="flex-1"></div>
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-800">
                                <span>{{ Auth::user()->name }}</span>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ Auth::user()->isAdmin() ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ['admin'=>'管理員','teacher'=>'老師','parent'=>'家長','student'=>'學生'][Auth::user()->role] ?? Auth::user()->role }}
                                </span>
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">個人資料</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">登出</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </header>

                {{-- Page heading --}}
                @isset($header)
                    <div class="bg-white border-b border-gray-100">
                        <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">{{ $header }}</div>
                    </div>
                @endisset

                {{-- Flash --}}
                @if (session('status'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                        <div class="rounded-md bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                        <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                        </div>
                    </div>
                @endif

                <main>{{ $slot }}</main>
            </div>
        </div>
    </body>
</html>
