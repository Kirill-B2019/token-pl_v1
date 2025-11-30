@php($title = 'Пакеты токенов (Vue + Bootstrap)')
<x-layouts.app :title="$title">
    <!-- |KB Пример использования Vue + Bootstrap компонента -->
    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <a href="{{ route('client.dashboard') }}" class="text-sm text-indigo-600 hover:underline">Назад к кабинету</a>
        </div>

        <!-- Vue компонент будет монтироваться здесь -->
        <div id="vue-packages-app" data-vue-app="PackagesList"></div>
    </div>
</x-layouts.app>


