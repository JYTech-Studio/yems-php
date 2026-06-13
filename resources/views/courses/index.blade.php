<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800">課程管理</h2>
            <a href="{{ route('courses.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">＋ 新增課程</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50/80 text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">課程名稱</th>
                            <th class="px-6 py-3 text-left font-medium">類型</th>
                            <th class="px-6 py-3 text-left font-medium">每包點數</th>
                            <th class="px-6 py-3 text-left font-medium">固定時段</th>
                            <th class="px-6 py-3 text-left font-medium">報名人數</th>
                            <th class="px-6 py-3 text-left font-medium">狀態</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($courses as $course)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3"><a href="{{ route('courses.show', $course) }}" class="text-emerald-700 hover:underline font-medium">{{ $course->name }}</a></td>
                                <td class="px-6 py-3"><span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $course->class_type === 'private' ? 'bg-violet-100 text-violet-700' : 'bg-sky-100 text-sky-700' }}">{{ $course->classTypeLabel() }}</span></td>
                                <td class="px-6 py-3 text-gray-600">{{ $course->credits_per_pack }} 點 / 包</td>
                                <td class="px-6 py-3 text-gray-600">{{ $course->schedules_count }} 段</td>
                                <td class="px-6 py-3 text-gray-600">{{ $course->enrollments_count }} 人</td>
                                <td class="px-6 py-3"><span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $course->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $course->is_active ? '開設中' : '已停開' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">尚無課程</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $courses->links() }}</div>
        </div>
    </div>
</x-app-layout>
