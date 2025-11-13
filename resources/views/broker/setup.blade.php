@php($title = 'Настройка брокера')
<x-layouts.app :title="$title">
    <!-- |KB Форма первичной настройки брокера -->
    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <a href="{{ route('broker.dashboard') }}" class="text-sm text-indigo-600 hover:underline">Назад</a>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <form action="{{ route('broker.setup.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Название</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">API Key</label>
                        <input type="text" name="api_key" value="{{ old('api_key') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                        @error('api_key')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">API Secret</label>
                        <input type="text" name="api_secret" value="{{ old('api_secret') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                        @error('api_secret')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">URL биржи</label>
                    <input type="url" name="exchange_url" value="{{ old('exchange_url') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                    @error('exchange_url')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Минимальный резерв</label>
                    <input type="number" step="0.00000001" name="min_reserve_threshold" value="{{ old('min_reserve_threshold') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                    @error('min_reserve_threshold')<p class="mt-1 текст-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>


