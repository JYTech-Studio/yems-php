@php($parent = $parent ?? null)
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="姓名 *" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $parent?->name)" required autofocus />
    </div>
    <div>
        <x-input-label for="phone" value="電話" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $parent?->phone)" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $parent?->email)" />
    </div>
</div>
