<x-portal-layout :token="$token">
    <h1 class="text-lg font-bold text-gray-800 mb-1">{{ $parent->name }} 您好</h1>
    <p class="text-sm text-gray-500 mb-5">點選孩子查看課表、出席、點數與請假</p>

    <div class="space-y-3">
        @forelse ($children as $child)
            @php($totalCredits = $child->enrollments->sum('credits_remaining'))
            @php($low = $child->enrollments->contains(fn ($e) => $e->credits_remaining <= 3))
            <a href="{{ route('portal.student', ['token' => $token, 'student' => $child]) }}"
               class="block rounded-xl border border-gray-100 shadow-sm p-4 hover:border-emerald-300 transition">
                <div class="flex items-center gap-3">
                    <span class="grid place-items-center h-11 w-11 rounded-full bg-emerald-100 text-emerald-700 font-bold">{{ mb_substr($child->name, 0, 1) }}</span>
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800">{{ $child->name }}</div>
                        <div class="text-xs text-gray-400">{{ $child->grade_level ?: '—' }} · {{ $child->enrollments->count() }} 門課</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold {{ $low ? 'text-amber-600' : 'text-emerald-600' }}">{{ $totalCredits }} 點</div>
                        @if ($low)<div class="text-[10px] text-amber-500">點數偏低</div>@endif
                    </div>
                </div>
            </a>
        @empty
            <p class="text-sm text-gray-400 text-center py-8">尚未綁定孩子</p>
        @endforelse
    </div>
</x-portal-layout>
