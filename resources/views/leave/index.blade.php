<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-2xl text-gray-800">請假管理</h2></x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- 登錄請假 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-1">登錄請假</h3>
                <p class="text-xs text-gray-400 mb-4">請假不扣點，僅追蹤補課狀態</p>
                <form method="POST" action="{{ route('leave.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <x-input-label value="學生 · 課程 *" />
                        <select name="enrollment_id" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">選擇…</option>
                            @foreach ($enrollments as $e)<option value="{{ $e->id }}">{{ $e->student->name }} · {{ $e->course->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="請假日期 *" />
                        <x-text-input name="leave_date" type="date" :value="now('Asia/Taipei')->format('Y-m-d')" class="mt-1 block w-full text-sm" required />
                    </div>
                    <div>
                        <x-input-label value="原因" />
                        <x-text-input name="reason" type="text" class="mt-1 block w-full text-sm" placeholder="選填" />
                    </div>
                    <button class="w-full px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">登錄</button>
                </form>
            </div>

            {{-- 列表 --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="flex gap-1 bg-gray-100 p-1 rounded-lg w-fit text-sm">
                    @foreach (['all'=>'全部','pending'=>'未補課','done'=>'已補課'] as $key => $label)
                        <a href="{{ route('leave.index', ['status'=>$key]) }}" class="px-4 py-1.5 rounded-md {{ $status===$key ? 'bg-white shadow-sm font-medium text-gray-800' : 'text-gray-500 hover:text-gray-700' }}">{{ $label }}</a>
                    @endforeach
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50/80 text-gray-500">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium">日期</th>
                                <th class="px-5 py-3 text-left font-medium">學生 / 課程</th>
                                <th class="px-5 py-3 text-left font-medium">原因</th>
                                <th class="px-5 py-3 text-left font-medium">補課</th>
                                <th class="px-5 py-3 text-right font-medium">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($leaves as $leave)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3 text-gray-600 whitespace-nowrap">{{ $leave->leave_date->format('Y-m-d') }}</td>
                                    <td class="px-5 py-3"><div class="text-gray-800">{{ $leave->student->name }}</div><div class="text-xs text-gray-400">{{ $leave->enrollment?->course?->name ?? '—' }}</div></td>
                                    <td class="px-5 py-3 text-gray-500">{{ $leave->reason ?: '—' }}</td>
                                    <td class="px-5 py-3">
                                        @if ($leave->is_made_up)
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs bg-emerald-100 text-emerald-700">已補 {{ $leave->made_up_date?->format('m/d') }}</span>
                                        @else
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs bg-amber-50 text-amber-600">未補課</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap">
                                        <form method="POST" action="{{ route('leave.update', $leave) }}" class="inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="is_made_up" value="{{ $leave->is_made_up ? 0 : 1 }}" />
                                            <button class="text-emerald-700 hover:underline text-xs">{{ $leave->is_made_up ? '取消補課' : '標記補課' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('leave.destroy', $leave) }}" class="inline ml-2" onsubmit="return confirm('刪除？')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-500 hover:underline text-xs">刪除</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">沒有請假紀錄</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div>{{ $leaves->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
