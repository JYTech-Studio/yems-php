<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800">{{ $parent->name }}</h2>
            <a href="{{ route('parents.edit', $parent) }}" class="px-3 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">編輯</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 綁定子女 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-4">綁定子女</h3>
                @forelse ($parent->children as $child)
                    <div class="flex items-center justify-between py-2 text-sm border-b border-gray-50 last:border-0">
                        <a href="{{ route('students.show', $child) }}" class="text-emerald-700 hover:underline">{{ $child->name }} <span class="text-gray-400">· {{ $child->grade_level ?: '—' }}</span></a>
                        <form method="POST" action="{{ route('parents.children.detach', [$parent, $child]) }}" onsubmit="return confirm('解除綁定？')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:underline text-xs">解除</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 mb-3">尚未綁定子女</p>
                @endforelse

                @if ($availableStudents->isNotEmpty())
                    <form method="POST" action="{{ route('parents.children.attach', $parent) }}" class="mt-4 flex gap-2 border-t border-gray-100 pt-4">
                        @csrf
                        <select name="student_id" required class="flex-1 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">選擇學生…</option>
                            @foreach ($availableStudents as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}（{{ $s->grade_level ?: '—' }}）</option>
                            @endforeach
                        </select>
                        <input name="relation" placeholder="關係（如 母）" class="w-32 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <button class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">綁定</button>
                    </form>
                @endif
            </div>

            {{-- 家長 Portal 連結 --}}
            @php($activeToken = $parent->accessTokens->firstWhere('is_active', true))
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">家長 Portal 連結</h3>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('parents.token.store', $parent) }}">
                            @csrf
                            <button class="px-3 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">{{ $activeToken ? '重新產生' : '產生連結' }}</button>
                        </form>
                        @if ($activeToken)
                            <form method="POST" action="{{ route('parents.token.destroy', $parent) }}" onsubmit="return confirm('撤銷後家長將無法用舊連結登入，確定？')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-2 bg-gray-100 text-gray-600 text-sm rounded-lg hover:bg-gray-200">撤銷</button>
                            </form>
                        @endif
                    </div>
                </div>
                @if ($activeToken)
                    <p class="text-xs text-gray-500 mb-2">把這個無登入連結給家長，即可查看孩子的課表 / 出席 / 點數 / 請假：</p>
                    <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2 font-mono text-xs text-gray-700 break-all">
                        {{ url('/p/'.$activeToken->token) }}
                    </div>
                    <p class="text-[11px] text-gray-400 mt-2">產生於 {{ $activeToken->created_at->format('Y-m-d H:i') }}（家長 Portal 頁面於 Phase I 啟用）</p>
                @else
                    <p class="text-sm text-gray-400">尚未產生連結。</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
