@php($student = $student ?? null)
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="姓名 *" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $student?->name)" required autofocus />
    </div>
    <div>
        <x-input-label for="grade_level" value="年級" />
        <x-text-input id="grade_level" name="grade_level" type="text" class="mt-1 block w-full" :value="old('grade_level', $student?->grade_level)" placeholder="如 國一" />
    </div>
    <div>
        <x-input-label for="phone" value="電話" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $student?->phone)" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="is_active" value="狀態 *" />
        <select id="is_active" name="is_active" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="1" @selected(old('is_active', $student?->is_active ?? 1) == 1)>在學</option>
            <option value="0" @selected(old('is_active', $student?->is_active ?? 1) == 0)>停課</option>
        </select>
    </div>
</div>
