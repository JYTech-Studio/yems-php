<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800">學生管理</h2>
            <a href="{{ route('students.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">＋ 新增學生</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ $q }}" placeholder="搜尋姓名 / 電話"
                       class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                <button class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">搜尋</button>
                @if ($q !== '')<a href="{{ route('students.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm rounded-lg">清除</a>@endif
            </form>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50/80 text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">姓名</th>
                            <th class="px-6 py-3 text-left font-medium">年級</th>
                            <th class="px-6 py-3 text-left font-medium">電話</th>
                            <th class="px-6 py-3 text-left font-medium">報名課程</th>
                            <th class="px-6 py-3 text-left font-medium">RFID</th>
                            <th class="px-6 py-3 text-left font-medium">狀態</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($students as $student)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3"><a href="{{ route('students.show', $student) }}" class="text-emerald-700 hover:underline font-medium">{{ $student->name }}</a></td>
                                <td class="px-6 py-3 text-gray-600">{{ $student->grade_level ?: '—' }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $student->phone ?: '—' }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $student->enrollments_count }} 門</td>
                                <td class="px-6 py-3 text-gray-600">{{ $student->rfid_cards_count }} 張</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $student->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $student->is_active ? '在學' : '停課' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">沒有符合的學生</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $students->links() }}</div>
        </div>
    </div>
</x-app-layout>
