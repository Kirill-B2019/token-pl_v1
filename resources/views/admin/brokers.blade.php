@php($title = 'Брокеры')
<x-layouts.app :title="$title">
    <!-- |KB Просмотр брокеров и их статусов -->
    <div class="space-y-6 p-6">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>

        <div class="grid gap-4 md:grid-cols-2">
            @forelse($brokers as $broker)
                <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $broker->name }}</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Пользователь: {{ $broker->user->name ?? '—' }}</p>
                        </div>
                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $broker->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' }}">
                            {{ $broker->is_active ? 'Активен' : 'Отключен' }}
                        </span>
                    </div>
                    <dl class="mt-4 space-y-2 text-sm text-zinc-700 dark:text-zinc-200">
                        <div class="flex justify-between">
                            <dt>API Key</dt>
                            <dd class="truncate">{{ $broker->api_key }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>URL биржи</dt>
                            <dd class="truncate">{{ $broker->exchange_url }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Резерв</dt>
                            <dd>{{ number_format($broker->reserve_balance, 4, '.', ' ') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Порог</dt>
                            <dd>{{ number_format($broker->min_reserve_threshold, 4, '.', ' ') }}</dd>
                        </div>
                    </dl>
                </div>
            @empty
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Брокеры не найдены.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>


