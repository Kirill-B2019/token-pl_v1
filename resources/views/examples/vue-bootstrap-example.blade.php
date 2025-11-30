@php($title = 'Vue + Bootstrap Пример')
<x-layouts.app :title="$title">
    <!-- |KB Демонстрация Vue + Bootstrap интеграции -->
    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
        </div>

        <!-- Vue компонент с примером -->
        <div id="vue-example-app" data-vue-app="ExampleComponent"></div>
    </div>
</x-layouts.app>


