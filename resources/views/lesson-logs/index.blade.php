<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-2xl text-gray-800">聯絡簿</h2></x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- tab --}}
            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg w-fit text-sm">
                <a href="{{ route('lesson-logs.index') }}" class="px-4 py-1.5 rounded-md bg-white shadow-sm font-medium text-gray-800">班級日誌</a>
                <a href="{{ route('contact-books.index') }}" class="px-4 py-1.5 rounded-md text-gray-500 hover:text-gray-700">個人聯絡簿</a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                {{-- 新增 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">新增班級日誌</h3>
                    <form method="POST" action="{{ route('lesson-logs.store') }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label value="課程 *" />
                            <select name="course_id" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">選擇課程…</option>
                                @foreach ($courses as $c)<option value="{{ $c->id }}" @selected($courseId===$c->id)>{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label value="上課日期 *" />
                            <x-text-input name="log_date" type="date" :value="now('Asia/Taipei')->format('Y-m-d')" class="mt-1 block w-full text-sm" required />
                        </div>
                        <div>
                            <x-input-label value="今日重點 *" />
                            <textarea name="summary" rows="3" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>
                        <div>
                            <x-input-label value="回家作業" />
                            <textarea name="homework" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>
                        <div>
                            <x-input-label value="上課照片（可多張）" />
                            <input type="file" name="photos[]" accept="image/*" multiple class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
                        </div>
                        <button class="w-full px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">新增</button>
                    </form>
                </div>

                {{-- 列表 --}}
                <div class="lg:col-span-2 space-y-4">
                    <form method="GET" class="flex gap-2">
                        <select name="course" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">全部課程</option>
                            @foreach ($courses as $c)<option value="{{ $c->id }}" @selected($courseId===$c->id)>{{ $c->name }}</option>@endforeach
                        </select>
                    </form>

                    @forelse ($logs as $log)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <a href="{{ route('lesson-logs.show', $log) }}" class="font-semibold text-gray-800 hover:text-emerald-700">{{ $log->course->name }}</a>
                                    <span class="text-xs text-gray-400 ml-2">{{ $log->log_date->format('Y-m-d') }} · {{ $log->creator->name }}</span>
                                </div>
                                @if ($log->photos->count())<span class="text-xs text-gray-400">📷 {{ $log->photos->count() }}</span>@endif
                            </div>
                            <p class="text-sm text-gray-600 line-clamp-2">{{ $log->summary }}</p>
                            <a href="{{ route('lesson-logs.show', $log) }}" class="inline-block mt-2 text-xs text-emerald-700 hover:underline">查看 →</a>
                        </div>
                    @empty
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-10 text-center text-sm text-gray-400">尚無班級日誌</div>
                    @endforelse
                    <div>{{ $logs->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
