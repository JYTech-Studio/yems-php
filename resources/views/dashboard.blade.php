<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">總覽</h2>
            <p class="text-sm text-gray-500 mt-1">{{ now('Asia/Taipei')->format('Y 年 n 月 j 日') }} · 補習班行政管理</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 統計卡片 --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
                @php($cards = [
                    ['在學學生', $stats['students'], '名在學中', 'bg-emerald-500'],
                    ['開設課程', $stats['courses'], '門課程', 'bg-sky-500'],
                    ['今日簽到', $stats['today_checkins'], '人次', 'bg-violet-500'],
                    ['點數不足', $stats['low_credit'], '個帳戶 ≤3 點', 'bg-amber-500'],
                ])
                @foreach ($cards as [$label, $value, $unit, $accent])
                    <div class="relative bg-white rounded-xl shadow-sm border border-gray-100 p-6 overflow-hidden">
                        <span class="absolute left-0 top-0 h-full w-1 {{ $accent }}"></span>
                        <div class="text-sm font-medium text-gray-500">{{ $label }}</div>
                        <div class="mt-3 text-4xl font-bold text-gray-800">{{ $value }}</div>
                        <div class="mt-1 text-xs text-gray-400">{{ $unit }}</div>
                    </div>
                @endforeach
            </div>

            {{-- 點數不足提醒 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <span class="text-amber-500">⚠️</span>
                    <span class="font-semibold text-gray-800">點數不足提醒</span>
                </div>
                @if ($lowCreditEnrollments->isEmpty())
                    <div class="px-6 py-12 text-center text-sm text-gray-400">目前沒有點數不足的帳戶 🎉</div>
                @else
                    <ul class="divide-y divide-gray-50">
                        @foreach ($lowCreditEnrollments as $enrollment)
                            <li class="px-6 py-3.5 flex items-center justify-between hover:bg-gray-50/60 transition">
                                <div class="flex items-center gap-3">
                                    <span class="grid place-items-center h-8 w-8 rounded-full bg-emerald-50 text-emerald-700 text-sm font-medium">
                                        {{ mb_substr($enrollment->student->name, 0, 1) }}
                                    </span>
                                    <div>
                                        <div class="text-gray-800">{{ $enrollment->student->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $enrollment->course->name }}</div>
                                    </div>
                                </div>
                                <span class="text-sm font-semibold px-2.5 py-1 rounded-full {{ $enrollment->credits_remaining <= 0 ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600' }}">
                                    剩 {{ $enrollment->credits_remaining }} 點
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
