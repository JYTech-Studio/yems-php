<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <nav class="text-sm text-gray-400">
                <a href="{{ route('lesson-logs.index') }}" class="hover:text-gray-600">聯絡簿</a>
                <span class="mx-1">/</span><span class="text-gray-600">{{ $lessonLog->course->name }}</span>
            </nav>
            <form method="POST" action="{{ route('lesson-logs.destroy', $lessonLog) }}" onsubmit="return confirm('刪除這篇聯絡簿？')">
                @csrf @method('DELETE')
                <button class="px-3 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">刪除</button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                    <div>
                        <div class="text-lg font-bold text-gray-800">{{ $lessonLog->course->name }}</div>
                        <div class="text-sm text-gray-400">{{ $lessonLog->log_date->format('Y 年 n 月 j 日') }} · {{ $lessonLog->creator->name }}</div>
                    </div>
                </div>
                <div class="space-y-4 text-sm">
                    <div>
                        <div class="font-medium text-gray-500 mb-1">今日重點</div>
                        <p class="text-gray-800 whitespace-pre-line">{{ $lessonLog->summary }}</p>
                    </div>
                    @if ($lessonLog->homework)
                        <div>
                            <div class="font-medium text-gray-500 mb-1">回家作業</div>
                            <p class="text-gray-800 whitespace-pre-line">{{ $lessonLog->homework }}</p>
                        </div>
                    @endif
                </div>

                @if ($lessonLog->photos->count())
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <div class="font-medium text-gray-500 mb-3 text-sm">上課照片</div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach ($lessonLog->photos as $photo)
                                <a href="{{ $photo->url() }}" target="_blank" class="block aspect-square rounded-lg overflow-hidden bg-gray-100">
                                    <img src="{{ $photo->url() }}" alt="" class="w-full h-full object-cover hover:scale-105 transition" />
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
