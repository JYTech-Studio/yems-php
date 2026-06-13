<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-2xl text-gray-800">新增學生</h2></x-slot>
    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('students.store') }}">
                    @csrf
                    @include('students.form')
                    <div class="mt-6 flex items-center gap-3">
                        <x-primary-button>新增</x-primary-button>
                        <a href="{{ route('students.index') }}" class="text-sm text-gray-500 hover:underline">取消</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
