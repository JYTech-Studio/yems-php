<x-app-layout>
    <x-slot name="header">
        <nav class="text-sm text-gray-400">
            <a href="{{ route('credits.index') }}" class="hover:text-gray-600">點數帳戶</a>
            <span class="mx-1">/</span><span class="text-gray-600">{{ $enrollment->student->name }} · {{ $enrollment->course->name }}</span>
        </nav>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- summary --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col sm:flex-row sm:items-center gap-5">
                <div class="flex-1">
                    <a href="{{ route('students.show', $enrollment->student) }}" class="text-lg font-bold text-gray-800 hover:text-emerald-700">{{ $enrollment->student->name }}</a>
                    <div class="text-sm text-gray-500 mt-0.5">{{ $enrollment->course->name }} · {{ $enrollment->current_material ?: '未設定進度' }}</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold {{ $enrollment->isLowCredit() ? 'text-amber-600' : 'text-emerald-600' }}">{{ $enrollment->credits_remaining }}</div>
                    <div class="text-xs text-gray-400">剩餘點數</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                {{-- 加扣點 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-3">手動加 / 扣點</h3>
                    <form method="POST" action="{{ route('credits.adjust', $enrollment) }}" class="space-y-3">
                        @csrf
                        <select name="direction" class="block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="add">加點</option>
                            <option value="deduct">扣點</option>
                        </select>
                        <x-text-input name="amount" type="number" min="1" class="block w-full text-sm" placeholder="點數" required />
                        <x-text-input name="note" type="text" class="block w-full text-sm" placeholder="備註（選填）" />
                        <button class="w-full px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">送出</button>
                    </form>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-400 mb-2">快速儲值</p>
                        <form method="POST" action="{{ route('credits.purchase') }}" class="flex gap-2">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $enrollment->student_id }}" />
                            <input type="hidden" name="course_id" value="{{ $enrollment->course_id }}" />
                            <input name="packs" type="number" min="1" value="1" class="flex-1 min-w-0 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            <button class="shrink-0 px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 whitespace-nowrap">儲值（包）</button>
                        </form>
                    </div>
                </div>

                {{-- 交易明細 --}}
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <h3 class="font-semibold text-gray-800 px-6 py-4 border-b border-gray-100">點數異動明細</h3>
                    @if ($enrollment->creditTransactions->isEmpty())
                        <p class="px-6 py-8 text-center text-sm text-gray-400">尚無異動紀錄</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50/80 text-gray-500">
                                <tr>
                                    <th class="px-6 py-2.5 text-left font-medium">時間</th>
                                    <th class="px-6 py-2.5 text-left font-medium">類型</th>
                                    <th class="px-6 py-2.5 text-right font-medium">異動</th>
                                    <th class="px-6 py-2.5 text-right font-medium">餘額</th>
                                    <th class="px-6 py-2.5 text-left font-medium">備註</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($enrollment->creditTransactions as $tx)
                                    <tr>
                                        <td class="px-6 py-2.5 text-gray-500 whitespace-nowrap">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="px-6 py-2.5 text-gray-700">{{ $tx->typeLabel() }}</td>
                                        <td class="px-6 py-2.5 text-right font-medium {{ $tx->amount >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $tx->amount > 0 ? '+' : '' }}{{ $tx->amount }}</td>
                                        <td class="px-6 py-2.5 text-right text-gray-600">{{ $tx->balance_after }}</td>
                                        <td class="px-6 py-2.5 text-gray-500">{{ $tx->note ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
