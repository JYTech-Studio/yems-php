<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800">帳號管理</h2>
            <a href="{{ route('accounts.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">＋ 新增帳號</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50/80 text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">姓名</th>
                            <th class="px-6 py-3 text-left font-medium">Email</th>
                            <th class="px-6 py-3 text-left font-medium">角色</th>
                            <th class="px-6 py-3 text-left font-medium">狀態</th>
                            <th class="px-6 py-3 text-right font-medium">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($accounts as $account)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-800">{{ $account->name }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $account->email }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $account->isAdmin() ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-700' }}">{{ $account->isAdmin() ? '管理員' : '老師' }}</span>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $account->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $account->is_active ? '啟用' : '停用' }}</span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('accounts.edit', $account) }}" class="text-emerald-700 hover:underline">編輯</a>
                                    @if ($account->id !== auth()->id())
                                        <form method="POST" action="{{ route('accounts.destroy', $account) }}" class="inline ml-2" onsubmit="return confirm('刪除此帳號？')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-500 hover:underline">刪除</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>{{ $accounts->links() }}</div>
        </div>
    </div>
</x-app-layout>
