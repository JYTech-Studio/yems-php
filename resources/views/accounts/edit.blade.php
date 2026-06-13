<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-2xl text-gray-800">編輯帳號 — {{ $account->name }}</h2></x-slot>
    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('accounts.update', $account) }}" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <x-input-label for="name" value="姓名 *" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $account->name)" required />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email *" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $account->email)" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="role" value="角色 *" />
                            <select id="role" name="role" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="teacher" @selected(old('role', $account->role)==='teacher')>老師</option>
                                <option value="admin" @selected(old('role', $account->role)==='admin')>管理員</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="is_active" value="狀態 *" />
                            <select id="is_active" name="is_active" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="1" @selected(old('is_active', $account->is_active)==1)>啟用</option>
                                <option value="0" @selected(old('is_active', $account->is_active)==0)>停用</option>
                            </select>
                        </div>
                    </div>
                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-xs text-gray-400 mb-2">如需改密碼才填，留空表示不變更</p>
                        <div class="grid grid-cols-2 gap-4">
                            <x-text-input name="password" type="password" class="block w-full" placeholder="新密碼" />
                            <x-text-input name="password_confirmation" type="password" class="block w-full" placeholder="確認新密碼" />
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <x-primary-button>儲存</x-primary-button>
                        <a href="{{ route('accounts.index') }}" class="text-sm text-gray-500 hover:underline">取消</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
