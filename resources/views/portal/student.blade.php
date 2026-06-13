<x-portal-layout :token="$token" :back="route('portal.home', $token)">
    <div x-data="{ tab: 'overview' }">
        <div class="flex items-center gap-3 mb-4">
            <span class="grid place-items-center h-12 w-12 rounded-full bg-emerald-100 text-emerald-700 text-lg font-bold">{{ mb_substr($student->name, 0, 1) }}</span>
            <div>
                <div class="text-lg font-bold text-gray-800">{{ $student->name }}</div>
                <div class="text-xs text-gray-400">{{ $student->grade_level ?: '—' }}</div>
            </div>
        </div>

        {{-- segmented tabs --}}
        <div class="flex gap-1 bg-gray-100 p-1 rounded-lg text-sm mb-4">
            @foreach (['overview'=>'總覽','attendance'=>'出席','logs'=>'聯絡簿','leave'=>'請假'] as $key => $label)
                <button @click="tab='{{ $key }}'" :class="tab==='{{ $key }}' ? 'bg-white shadow-sm text-gray-800 font-medium' : 'text-gray-500'"
                        class="flex-1 px-2 py-1.5 rounded-md transition">{{ $label }}</button>
            @endforeach
        </div>

        {{-- 總覽 --}}
        <div x-show="tab==='overview'" class="space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">點數帳戶</h3>
                @forelse ($student->enrollments as $e)
                    <div class="flex items-center justify-between rounded-lg border border-gray-100 px-4 py-2.5 mb-2">
                        <span class="text-sm text-gray-800">{{ $e->course->name }}</span>
                        <span class="text-sm font-semibold px-2 py-0.5 rounded-full {{ $e->credits_remaining <= 3 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-700' }}">{{ $e->credits_remaining }} 點</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">尚未報名課程</p>
                @endforelse
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">📅 固定課表</h3>
                @forelse ($schedule as $s)
                    <div class="flex items-center justify-between text-sm border-b border-gray-50 py-2">
                        <span class="text-gray-700">{{ ['','週一','週二','週三','週四','週五','週六','週日'][$s['weekday']] }} {{ $s['start'] }}–{{ $s['end'] }}</span>
                        <span class="text-gray-400">{{ $s['course'] }} {{ $s['room'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">尚無固定課表</p>
                @endforelse
            </div>
        </div>

        {{-- 出席 --}}
        <div x-show="tab==='attendance'" x-cloak class="space-y-2">
            @forelse ($student->attendanceRecords as $rec)
                <div class="flex items-center justify-between text-sm border-b border-gray-50 py-2.5">
                    <span class="text-gray-700">{{ $rec->recorded_at->format('m/d H:i') }} <span class="text-gray-400">{{ $rec->enrollment?->course?->name }}</span></span>
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $rec->record_type==='check_in' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $rec->typeLabel() }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-6">尚無出席紀錄</p>
            @endforelse
        </div>

        {{-- 聯絡簿 --}}
        <div x-show="tab==='logs'" x-cloak class="space-y-3">
            @forelse ($contactBooks as $cb)
                <div class="rounded-lg border border-gray-100 p-3">
                    <div class="text-xs text-gray-400 mb-1">{{ $cb->lesson_date->format('Y-m-d') }}{{ $cb->course ? ' · '.$cb->course->name : '' }}</div>
                    <p class="text-sm text-gray-700">{{ $cb->content }}</p>
                    @if ($cb->homework)<p class="text-xs text-emerald-700 mt-1">作業：{{ $cb->homework }}</p>@endif
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-6">尚無聯絡簿</p>
            @endforelse
        </div>

        {{-- 請假 --}}
        <div x-show="tab==='leave'" x-cloak class="space-y-4">
            <div class="rounded-lg border border-gray-100 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">線上請假</h3>
                <form method="POST" action="{{ route('portal.leave', ['token'=>$token, 'student'=>$student]) }}" class="space-y-2.5">
                    @csrf
                    <select name="enrollment_id" required class="block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">選擇課程…</option>
                        @foreach ($student->enrollments as $e)<option value="{{ $e->id }}">{{ $e->course->name }}</option>@endforeach
                    </select>
                    <input name="leave_date" type="date" required value="{{ now('Asia/Taipei')->format('Y-m-d') }}" class="block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                    <input name="reason" placeholder="原因（選填）" class="block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                    <button class="w-full px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">送出請假</button>
                </form>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">請假紀錄</h3>
                @forelse ($student->leaveRecords as $leave)
                    <div class="flex items-center justify-between text-sm border-b border-gray-50 py-2">
                        <span class="text-gray-700">{{ $leave->leave_date->format('Y-m-d') }} <span class="text-gray-400">{{ $leave->enrollment?->course?->name }}</span></span>
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $leave->is_made_up ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-50 text-amber-600' }}">{{ $leave->is_made_up ? '已補課' : '未補課' }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">尚無請假紀錄</p>
                @endforelse
            </div>
        </div>
    </div>
</x-portal-layout>
