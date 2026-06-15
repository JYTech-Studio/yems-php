<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- 示範帳號（作品集 Demo 用，方便檢視者直接登入）--}}
    <div class="mb-5 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
        <div class="font-semibold mb-1">🔑 示範帳號</div>
        <div class="space-y-0.5 font-mono text-[13px]">
            <div>管理員　admin@demo.com / Demo1234</div>
            <div>老師　　teacher@demo.com / Demo1234</div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2.5 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">密碼</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
        </div>

        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                <span class="ms-2 text-sm text-gray-600">記住我</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-emerald-700 hover:text-emerald-800 hover:underline" href="{{ route('password.request') }}">
                    忘記密碼？
                </a>
            @endif
        </div>

        <button class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-emerald-600 text-white font-medium rounded-lg
                       hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition">
            登入
        </button>
    </form>
</x-guest-layout>
