<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <nav class="text-sm text-gray-400">
                <a href="{{ route('students.index') }}" class="hover:text-gray-600">學生</a>
                <span class="mx-1">/</span><span class="text-gray-600">{{ $student->name }}</span>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('students.edit', $student) }}" class="px-3 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">編輯</a>
                @if (auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('students.destroy', $student) }}" onsubmit="return confirm('確定刪除這位學生？相關報名 / 點數紀錄也會一併刪除。')">
                        @csrf @method('DELETE')
                        <button class="px-3 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">刪除</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 頁首 summary --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <div class="flex items-center gap-4 flex-1">
                        <span class="grid place-items-center h-14 w-14 rounded-full bg-emerald-100 text-emerald-700 text-xl font-bold">{{ mb_substr($student->name, 0, 1) }}</span>
                        <div>
                            <div class="text-xl font-bold text-gray-800">{{ $student->name }}</div>
                            <div class="mt-1 flex items-center gap-2 text-sm">
                                <span class="text-gray-500">{{ $student->grade_level ?: '未填年級' }}</span>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $student->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $student->is_active ? '在學' : '停課' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-6 sm:gap-8 text-center">
                        @php($stats = [
                            ['報名課程', $student->enrollments->count()],
                            ['總點數', $student->enrollments->sum('credits_remaining')],
                            ['RFID 卡', $student->rfidCards->count()],
                            ['出席次數', $student->attendance_records_count],
                        ])
                        @foreach ($stats as [$l, $v])
                            <div>
                                <div class="text-2xl font-bold text-gray-800">{{ $v }}</div>
                                <div class="text-xs text-gray-400 mt-0.5 whitespace-nowrap">{{ $l }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                {{-- 左欄 --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">基本資料</h3>
                        <dl class="space-y-2.5 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">電話</dt><dd class="text-gray-800">{{ $student->phone ?: '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">年級</dt><dd class="text-gray-800">{{ $student->grade_level ?: '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">狀態</dt><dd class="text-gray-800">{{ $student->is_active ? '在學' : '停課' }}</dd></div>
                        </dl>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-3">RFID 卡片</h3>
                        @forelse ($student->rfidCards as $card)
                            <div class="flex items-center justify-between py-2 text-sm border-b border-gray-50 last:border-0">
                                <div class="min-w-0"><span class="font-mono text-gray-700">{{ $card->card_uid }}</span> <span class="text-gray-400">· {{ $card->label ?: '卡片' }}</span></div>
                                <form method="POST" action="{{ route('students.rfid.destroy', [$student, $card]) }}" onsubmit="return confirm('解除此卡綁定？')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:underline text-xs shrink-0 ml-2">解除</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 mb-1">尚未綁定卡片</p>
                        @endforelse
                        <form method="POST" action="{{ route('students.rfid.store', $student) }}" class="mt-3 border-t border-gray-100 pt-3 space-y-2">
                            @csrf
                            <input name="card_uid" placeholder="卡號 UID" required class="block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            <div class="flex gap-2">
                                <input name="label" placeholder="標籤（如 悠遊卡）" class="flex-1 min-w-0 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                <button class="shrink-0 px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700 whitespace-nowrap">綁定</button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-3">家長</h3>
                        @forelse ($student->parents as $parent)
                            <div class="flex items-center justify-between py-1.5 text-sm">
                                <a href="{{ route('parents.show', $parent) }}" class="text-emerald-700 hover:underline">{{ $parent->name }}</a>
                                <span class="text-gray-400 text-xs">{{ $parent->pivot->relation }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">尚未綁定家長（可在「家長管理」綁定）</p>
                        @endforelse
                    </div>
                </div>

                {{-- 右欄 --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">點數帳戶（依課程）</h3>
                            <span class="text-xs text-gray-400">儲值 / 加扣點將於「點數」頁操作</span>
                        </div>
                        @forelse ($student->enrollments as $enrollment)
                            <div class="px-6 py-3 flex items-center justify-between border-b border-gray-50 last:border-0">
                                <div><div class="text-gray-800">{{ $enrollment->course->name }}</div><div class="text-xs text-gray-400">{{ $enrollment->current_material ?: '未設定進度' }}</div></div>
                                <span class="text-sm font-semibold px-2.5 py-1 rounded-full {{ $enrollment->isLowCredit() ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-700' }}">{{ $enrollment->credits_remaining }} 點</span>
                            </div>
                        @empty
                            <p class="px-6 py-6 text-center text-sm text-gray-400">尚未報名任何課程</p>
                        @endforelse
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <h3 class="font-semibold text-gray-800 px-5 py-3.5 border-b border-gray-100 text-sm">最近出席</h3>
                            @forelse ($student->attendanceRecords as $rec)
                                <div class="px-5 py-2.5 flex items-center justify-between text-sm border-b border-gray-50 last:border-0">
                                    <span class="text-gray-500 text-xs">{{ $rec->recorded_at->format('m/d H:i') }}</span>
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $rec->record_type === 'check_in' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $rec->typeLabel() }}</span>
                                </div>
                            @empty
                                <p class="px-5 py-6 text-center text-xs text-gray-400">尚無出席紀錄</p>
                            @endforelse
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <h3 class="font-semibold text-gray-800 px-5 py-3.5 border-b border-gray-100 text-sm">請假紀錄</h3>
                            @forelse ($student->leaveRecords as $leave)
                                <div class="px-5 py-2.5 flex items-center justify-between text-sm border-b border-gray-50 last:border-0">
                                    <span class="text-gray-500 text-xs">{{ $leave->leave_date->format('Y/m/d') }}</span>
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $leave->is_made_up ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-50 text-amber-600' }}">{{ $leave->is_made_up ? '已補課' : '未補課' }}</span>
                                </div>
                            @empty
                                <p class="px-5 py-6 text-center text-xs text-gray-400">尚無請假紀錄</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
