<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-2xl text-gray-800">新增帳號</h2></x-slot>
    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('accounts.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="name" value="姓名 *" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email *" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                    </div>
                    <div>
                        <x-input-label for="role" value="角色 *" />
                        <select id="role" name="role" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="teacher" @selected(old('role')==='teacher')>老師</option>
                            <option value="admin" @selected(old('role')==='admin')>管理員</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="password" value="密碼 *" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" value="確認密碼 *" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <x-primary-button>建立</x-primary-button>
                        <a href="{{ route('accounts.index') }}" class="text-sm text-gray-500 hover:underline">取消</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
