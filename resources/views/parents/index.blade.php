<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800">家長管理</h2>
            <a href="{{ route('parents.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">＋ 新增家長</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ $q }}" placeholder="搜尋姓名" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                <button class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">搜尋</button>
            </form>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50/80 text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">姓名</th>
                            <th class="px-6 py-3 text-left font-medium">電話</th>
                            <th class="px-6 py-3 text-left font-medium">Email</th>
                            <th class="px-6 py-3 text-left font-medium">綁定子女</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($parents as $parent)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3"><a href="{{ route('parents.show', $parent) }}" class="text-emerald-700 hover:underline font-medium">{{ $parent->name }}</a></td>
                                <td class="px-6 py-3 text-gray-600">{{ $parent->phone ?: '—' }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $parent->email ?: '—' }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $parent->children_count }} 位</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400">沒有家長資料</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $parents->links() }}</div>
        </div>
    </div>
</x-app-layout>
