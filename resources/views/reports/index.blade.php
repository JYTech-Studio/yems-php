<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-2xl text-gray-800">報表</h2></x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 日期範圍 --}}
            <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label value="起" />
                    <x-text-input name="from" type="date" :value="$from->format('Y-m-d')" class="mt-1 block text-sm" />
                </div>
                <div>
                    <x-input-label value="迄" />
                    <x-text-input name="to" type="date" :value="$to->format('Y-m-d')" class="mt-1 block text-sm" />
                </div>
                <button class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">套用</button>
                <span class="text-xs text-gray-400 self-center">預設本月；範圍套用到下方財務與匯出</span>
            </form>

            {{-- 財務總覽 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-4">財務總覽（儲值）</h3>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-5">
                    @foreach ([
                        ['儲值筆數', $finance['purchase_count'], ''],
                        ['實收金額', 'NT$ '.number_format($finance['total_paid']), 'text-emerald-600'],
                        ['定價總額', 'NT$ '.number_format($finance['total_list_price']), ''],
                        ['折扣總額', 'NT$ '.number_format($finance['total_discount']), 'text-amber-600'],
                        ['售出點數', $finance['total_credits_purchased'], ''],
                    ] as [$label, $value, $color])
                        <div>
                            <div class="text-xs text-gray-400">{{ $label }}</div>
                            <div class="mt-1 text-xl font-bold {{ $color ?: 'text-gray-800' }}">{{ $value }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 匯出 --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-1">點數異動報表</h3>
                    <p class="text-xs text-gray-400 mb-4">所有儲值 / 扣點 / 調整明細</p>
                    <div class="flex gap-2">
                        <a href="{{ route('reports.credit-transactions', ['from'=>$from->format('Y-m-d'),'to'=>$to->format('Y-m-d'),'format'=>'csv']) }}" class="px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">⬇ CSV</a>
                        <a href="{{ route('reports.credit-transactions', ['from'=>$from->format('Y-m-d'),'to'=>$to->format('Y-m-d'),'format'=>'xlsx']) }}" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">⬇ Excel</a>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-1">請假補課報表</h3>
                    <p class="text-xs text-gray-400 mb-4">請假紀錄與補課狀態</p>
                    <div class="flex gap-2">
                        <a href="{{ route('reports.leave-records', ['from'=>$from->format('Y-m-d'),'to'=>$to->format('Y-m-d'),'format'=>'csv']) }}" class="px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">⬇ CSV</a>
                        <a href="{{ route('reports.leave-records', ['from'=>$from->format('Y-m-d'),'to'=>$to->format('Y-m-d'),'format'=>'xlsx']) }}" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">⬇ Excel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
