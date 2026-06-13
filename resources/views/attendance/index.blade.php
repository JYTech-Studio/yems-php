<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-2xl text-gray-800">點名工作檯</h2>
            <p class="text-sm text-gray-500 mt-0.5">模擬 RFID 刷卡 · {{ now('Asia/Taipei')->format('Y-m-d（D）') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

            {{-- 刷卡區 --}}
            <div class="lg:col-span-3 space-y-6">
                {{-- 結果橫幅 --}}
                @if (session('scan_result'))
                    @php($r = session('scan_result'))
                    <div class="rounded-xl px-5 py-4 text-sm font-medium border
                        {{ $r['action'] === 'check_in' ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                         : ($r['action'] === 'check_out' ? 'bg-gray-50 border-gray-200 text-gray-700' : 'bg-amber-50 border-amber-200 text-amber-800') }}">
                        {{ $r['message'] }}
                    </div>
                @endif
                @if (session('scan_error'))
                    <div class="rounded-xl px-5 py-4 text-sm font-medium bg-red-50 border border-red-200 text-red-700">❌ {{ session('scan_error') }}</div>
                @endif

                {{-- 刷卡輸入 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-1">刷卡 / 輸入卡號</h3>
                    <p class="text-xs text-gray-400 mb-4">輸入卡號按「刷卡」，系統自動判斷簽到 / 簽退並扣點</p>
                    <form method="POST" action="{{ route('attendance.scan') }}" class="flex gap-2">
                        @csrf
                        <input name="card_uid" autofocus autocomplete="off" placeholder="例如 CARD-0001"
                               class="flex-1 min-w-0 rounded-lg border-gray-300 text-lg font-mono focus:border-emerald-500 focus:ring-emerald-500" />
                        <button class="shrink-0 px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium">刷卡</button>
                    </form>
                </div>

                {{-- 一鍵模擬刷卡（卡片清單）--}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-1">模擬卡片（點一下＝刷卡）</h3>
                    <p class="text-xs text-gray-400 mb-4">沒有實體讀卡機，點下方卡片即可模擬該生刷卡</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach ($cards as $card)
                            <form method="POST" action="{{ route('attendance.scan') }}">
                                @csrf
                                <input type="hidden" name="card_uid" value="{{ $card->card_uid }}" />
                                <button class="w-full text-left px-3 py-2.5 rounded-lg border border-gray-200 hover:border-emerald-400 hover:bg-emerald-50/50 transition">
                                    <div class="text-sm font-medium text-gray-800">{{ $card->student->name }}</div>
                                    <div class="text-[11px] font-mono text-gray-400">{{ $card->card_uid }}</div>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 今日紀錄 --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <h3 class="font-semibold text-gray-800 px-6 py-4 border-b border-gray-100">今日刷卡紀錄</h3>
                @forelse ($todayRecords as $rec)
                    <div class="px-6 py-3 flex items-center justify-between text-sm border-b border-gray-50 last:border-0">
                        <div>
                            <div class="font-medium text-gray-800">{{ $rec->student->name }}</div>
                            <div class="text-xs text-gray-400">{{ $rec->enrollment?->course?->name ?? '—' }} · {{ $rec->recorded_at->format('H:i') }}</div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $rec->record_type === 'check_in' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $rec->typeLabel() }}</span>
                            @if (auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('attendance.cancel', $rec) }}"
                                      onsubmit="return confirm('確定作廢這筆{{ $rec->typeLabel() }}紀錄？{{ $rec->record_type === 'check_in' ? '已扣的 1 點會退回。' : '' }}')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-gray-400 hover:text-red-600 transition" title="作廢">作廢</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-gray-400">今天還沒有刷卡紀錄</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
