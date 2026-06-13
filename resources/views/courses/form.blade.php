@php($course = $course ?? null)
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="課程名稱 *" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $course?->name)" required autofocus />
    </div>
    <div>
        <x-input-label for="class_type" value="類型 *" />
        <select id="class_type" name="class_type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="group" @selected(old('class_type', $course?->class_type)==='group')>團班</option>
            <option value="private" @selected(old('class_type', $course?->class_type)==='private')>個人班</option>
        </select>
    </div>
    <div>
        <x-input-label for="is_active" value="狀態 *" />
        <select id="is_active" name="is_active" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="1" @selected(old('is_active', $course?->is_active ?? 1)==1)>開設中</option>
            <option value="0" @selected(old('is_active', $course?->is_active ?? 1)==0)>已停開</option>
        </select>
    </div>
    <div>
        <x-input-label for="credits_per_pack" value="每包點數 *" />
        <x-text-input id="credits_per_pack" name="credits_per_pack" type="number" min="1" class="mt-1 block w-full" :value="old('credits_per_pack', $course?->credits_per_pack ?? 20)" required />
    </div>
    <div>
        <x-input-label for="price_per_pack" value="每包價格（元）" />
        <x-text-input id="price_per_pack" name="price_per_pack" type="number" min="0" class="mt-1 block w-full" :value="old('price_per_pack', $course?->price_per_pack)" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="description" value="課程說明" />
        <textarea id="description" name="description" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description', $course?->description) }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="schedule_note" value="課表備註" />
        <x-text-input id="schedule_note" name="schedule_note" type="text" class="mt-1 block w-full" :value="old('schedule_note', $course?->schedule_note)" placeholder="特殊說明 / 過渡備註" />
    </div>
</div>
