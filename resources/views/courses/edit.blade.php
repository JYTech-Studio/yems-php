<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-2xl text-gray-800">編輯課程 — {{ $course->name }}</h2></x-slot>
    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('courses.update', $course) }}">
                    @csrf @method('PUT')
                    @include('courses.form')
                    <div class="mt-6 flex items-center gap-3">
                        <x-primary-button>儲存</x-primary-button>
                        <a href="{{ route('courses.show', $course) }}" class="text-sm text-gray-500 hover:underline">取消</a>
                    </div>
                </form>
                @if (auth()->user()->isAdmin())
                    <div class="mt-6 pt-4 border-t border-gray-100 text-right">
                        <form method="POST" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('確定刪除這門課程？')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline text-sm">刪除課程</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
