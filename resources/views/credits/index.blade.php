<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-2xl text-gray-800">點數帳戶</h2></x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- 儲值面板（同時可新增報名）--}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-1">儲值 / 報名</h3>
                <p class="text-xs text-gray-400 mb-4">選學生 + 課程儲值；若尚未報名會自動建立帳戶</p>
                <form method="POST" action="{{ route('credits.purchase') }}" class="space-y-3">
                    @csrf
                    <div>
                        <x-input-label value="學生 *" />
                        <select name="student_id" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">選擇學生…</option>
                            @foreach ($students as $s)<option value="{{ $s->id }}">{{ $s->name }}（{{ $s->grade_level ?: '—' }}）</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="課程 *" />
                        <select name="course_id" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">選擇課程…</option>
                            @foreach ($courses as $c)<option value="{{ $c->id }}">{{ $c->name }}（{{ $c->credits_per_pack }} 點/包）</option>@endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <x-input-label value="包數 *" />
                            <x-text-input name="packs" type="number" min="1" value="1" class="mt-1 block w-full text-sm" required />
                        </div>
                        <div>
                            <x-input-label value="實收金額" />
                            <x-text-input name="paid" type="number" min="0" class="mt-1 block w-full text-sm" placeholder="選填" />
                        </div>
                    </div>
                    <div>
                        <x-input-label value="備註" />
                        <x-text-input name="note" type="text" class="mt-1 block w-full text-sm" placeholder="選填" />
                    </div>
                    <button class="w-full px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">儲值</button>
                </form>
            </div>

            {{-- 帳戶列表 --}}
            <div class="lg:col-span-2 space-y-4">
                <form method="GET" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="q" value="{{ $q }}" placeholder="搜尋學生" class="flex-1 min-w-0 rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                    <label class="inline-flex items-center gap-1.5 text-sm text-gray-600">
                        <input type="checkbox" name="low" value="1" @checked($low) class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" /> 只看點數不足
                    </label>
                    <button class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">篩選</button>
                </form>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50/80 text-gray-500">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">學生</th>
                                <th class="px-6 py-3 text-left font-medium">課程</th>
                                <th class="px-6 py-3 text-left font-medium">餘額</th>
                                <th class="px-6 py-3 text-right font-medium">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($enrollments as $enrollment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-800">{{ $enrollment->student->name }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $enrollment->course->name }}</td>
                                    <td class="px-6 py-3"><span class="font-semibold px-2 py-0.5 rounded-full text-xs {{ $enrollment->isLowCredit() ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-700' }}">{{ $enrollment->credits_remaining }} 點</span></td>
                                    <td class="px-6 py-3 text-right"><a href="{{ route('credits.show', $enrollment) }}" class="text-emerald-700 hover:underline">明細 / 加扣點</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400">沒有符合的帳戶</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div>{{ $enrollments->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
