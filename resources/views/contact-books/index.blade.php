<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-2xl text-gray-800">聯絡簿</h2></x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg w-fit text-sm">
                <a href="{{ route('lesson-logs.index') }}" class="px-4 py-1.5 rounded-md text-gray-500 hover:text-gray-700">班級日誌</a>
                <a href="{{ route('contact-books.index') }}" class="px-4 py-1.5 rounded-md bg-white shadow-sm font-medium text-gray-800">個人聯絡簿</a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">新增個人聯絡簿</h3>
                    <form method="POST" action="{{ route('contact-books.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label value="學生 *" />
                            <select name="student_id" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">選擇學生…</option>
                                @foreach ($students as $s)<option value="{{ $s->id }}" @selected($studentId===$s->id)>{{ $s->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label value="課程（選填）" />
                            <select name="course_id" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">不指定</option>
                                @foreach ($courses as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label value="日期 *" />
                            <x-text-input name="lesson_date" type="date" :value="now('Asia/Taipei')->format('Y-m-d')" class="mt-1 block w-full text-sm" required />
                        </div>
                        <div>
                            <x-input-label value="內容 *" />
                            <textarea name="content" rows="3" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>
                        <div>
                            <x-input-label value="回家作業" />
                            <textarea name="homework" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="is_visible_to_parent" value="1" checked class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" /> 開放家長查看
                        </label>
                        <button class="w-full px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">新增</button>
                    </form>
                </div>

                <div class="lg:col-span-2 space-y-4">
                    @forelse ($entries as $entry)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <a href="{{ route('students.show', $entry->student) }}" class="font-semibold text-gray-800 hover:text-emerald-700">{{ $entry->student->name }}</a>
                                    <span class="text-xs text-gray-400 ml-2">{{ $entry->lesson_date->format('Y-m-d') }}{{ $entry->course ? ' · '.$entry->course->name : '' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @unless ($entry->is_visible_to_parent)<span class="text-[11px] text-gray-400">🔒 家長不可見</span>@endunless
                                    <form method="POST" action="{{ route('contact-books.destroy', $entry) }}" onsubmit="return confirm('刪除？')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-500 hover:underline text-xs">刪除</button>
                                    </form>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600">{{ $entry->content }}</p>
                            @if ($entry->homework)<p class="text-xs text-gray-400 mt-1">作業：{{ $entry->homework }}</p>@endif
                        </div>
                    @empty
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-10 text-center text-sm text-gray-400">尚無個人聯絡簿</div>
                    @endforelse
                    <div>{{ $entries->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
