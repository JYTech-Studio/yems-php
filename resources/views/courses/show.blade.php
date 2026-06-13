<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <nav class="text-sm text-gray-400">
                <a href="{{ route('courses.index') }}" class="hover:text-gray-600">課程</a>
                <span class="mx-1">/</span><span class="text-gray-600">{{ $course->name }}</span>
            </nav>
            <a href="{{ route('courses.edit', $course) }}" class="px-3 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">編輯</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- summary --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-bold text-gray-800">{{ $course->name }}</span>
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $course->class_type === 'private' ? 'bg-violet-100 text-violet-700' : 'bg-sky-100 text-sky-700' }}">{{ $course->classTypeLabel() }}</span>
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $course->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $course->is_active ? '開設中' : '已停開' }}</span>
                        </div>
                        @if ($course->description)<p class="mt-1.5 text-sm text-gray-500">{{ $course->description }}</p>@endif
                    </div>
                    <div class="grid grid-cols-3 gap-8 text-center">
                        @foreach ([['報名學生', $course->enrollments->count()], ['固定時段', $course->schedules->count()], ['每包點數', $course->credits_per_pack]] as [$l, $v])
                            <div><div class="text-2xl font-bold text-gray-800">{{ $v }}</div><div class="text-xs text-gray-400 mt-0.5 whitespace-nowrap">{{ $l }}</div></div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                {{-- 固定時段管理 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-3">固定時段</h3>
                    @forelse ($course->schedules as $sch)
                        <div class="flex items-center justify-between py-2 text-sm border-b border-gray-50 last:border-0">
                            <div>
                                <span class="font-medium text-gray-800">{{ $sch->weekdayLabel() }}</span>
                                <span class="text-gray-600">{{ substr($sch->start_time,0,5) }}–{{ substr($sch->end_time,0,5) }}</span>
                                @if ($sch->room)<span class="text-gray-400">· {{ $sch->room }}</span>@endif
                            </div>
                            <form method="POST" action="{{ route('courses.schedules.destroy', [$course, $sch]) }}" onsubmit="return confirm('刪除此時段？')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:underline text-xs shrink-0 ml-2">刪除</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 mb-1">尚未設定固定時段</p>
                    @endforelse

                    <form method="POST" action="{{ route('courses.schedules.store', $course) }}" class="mt-3 border-t border-gray-100 pt-3 space-y-2">
                        @csrf
                        <select name="weekday" required class="block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach (['1'=>'週一','2'=>'週二','3'=>'週三','4'=>'週四','5'=>'週五','6'=>'週六','7'=>'週日'] as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                        <div class="flex gap-2">
                            <input name="start_time" type="time" required class="flex-1 min-w-0 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            <span class="self-center text-gray-400 text-sm">–</span>
                            <input name="end_time" type="time" required class="flex-1 min-w-0 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div class="flex gap-2">
                            <input name="room" placeholder="教室（選填）" class="flex-1 min-w-0 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            <button class="shrink-0 px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700 whitespace-nowrap">新增</button>
                        </div>
                    </form>
                </div>

                {{-- 右欄：課表預覽 + 報名學生 --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">未來 20 天課表預覽</h3>
                            <span class="text-xs text-gray-400">依固定時段自動推算</span>
                        </div>
                        @if (empty($upcoming))
                            <p class="px-6 py-6 text-center text-sm text-gray-400">設定固定時段後，這裡會列出未來 20 天的上課日期</p>
                        @else
                            <ul class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                                @foreach ($upcoming as $s)
                                    <li class="px-6 py-2.5 flex items-center justify-between text-sm">
                                        <span class="text-gray-700">{{ $s['date']->format('n/j') }}（{{ ['','一','二','三','四','五','六','日'][$s['weekday']] }}）</span>
                                        <span class="text-gray-600">{{ $s['start_time'] }}–{{ $s['end_time'] }} <span class="text-gray-400">{{ $s['room'] }}</span></span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <h3 class="font-semibold text-gray-800 px-6 py-4 border-b border-gray-100">報名學生（{{ $course->enrollments->count() }}）</h3>
                        @forelse ($course->enrollments as $enrollment)
                            <div class="px-6 py-2.5 flex items-center justify-between text-sm border-b border-gray-50 last:border-0">
                                <a href="{{ route('students.show', $enrollment->student) }}" class="text-emerald-700 hover:underline">{{ $enrollment->student->name }}</a>
                                <span class="font-medium px-2 py-0.5 rounded-full text-xs {{ $enrollment->isLowCredit() ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-700' }}">{{ $enrollment->credits_remaining }} 點</span>
                            </div>
                        @empty
                            <p class="px-6 py-6 text-center text-sm text-gray-400">尚無學生報名</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
