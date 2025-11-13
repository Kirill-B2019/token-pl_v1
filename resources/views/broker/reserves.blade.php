@php($title = 'Резервы токенов')
<x-layouts.app :title="$title">
    <!-- |KB Управление резервами токенов брокером -->
    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <a href="{{ route('broker.dashboard') }}" class="text-sm text-indigo-600 hover:underline">Назад</a>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @forelse($tokens as $token)
                <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $token->name }}</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $token->symbol }}</p>
                        </div>
                        <p class="text-xl font-semibold text-zinc-900 dark:text-white">{{ number_format($token->available_supply, 4, '.', ' ') }}</p>
                    </div>
                    <form action="{{ route('broker.reserves.add', $token) }}" method="POST" class="mt-3 space-y-2">
                        @csrf
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-300">Добавить в резерв</label>
                        <input type="number" step="0.00000001" name="amount" class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                        <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Пополнить</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Токены недоступны.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>


