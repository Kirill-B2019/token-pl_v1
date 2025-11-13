@php($title = 'Системные настройки')
<x-layouts.app :title="$title">
    <!-- |KB Страница настроек системы -->
    <div class="space-y-6 p-6">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>

        @if(session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if(session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Название системы</label>
                    <input type="text" name="system_name" value="{{ old('system_name', config('app.name')) }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                    @error('system_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="maintenance_mode" value="0">
                    <input type="checkbox" name="maintenance_mode" value="1" {{ old('maintenance_mode') ? 'checked' : '' }} class="h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800">
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">Режим обслуживания</span>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="registration_enabled" value="0">
                    <input type="checkbox" name="registration_enabled" value="1" {{ old('registration_enabled', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800">
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">Разрешить регистрацию пользователей</span>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>


