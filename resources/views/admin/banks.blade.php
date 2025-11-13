@php($title = 'Банки')
<x-layouts.app :title="$title">
    <!-- |KB Управление банками-эквайерами -->
    <div class="space-y-6 p-6">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>

        <div class="grid gap-4 md:grid-cols-2">
            @forelse($banks as $bank)
                <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $bank->name }}</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Код: {{ $bank->code }}</p>
                        </div>
                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $bank->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' }}">
                            {{ $bank->is_active ? 'Активен' : 'Отключен' }}
                        </span>
                    </div>
                    <dl class="mt-4 space-y-2 text-sm text-zinc-700 dark:text-zinc-200">
                        <div class="flex justify-between">
                            <dt>Марчант</dt>
                            <dd>{{ $bank->merchant_id }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Комиссия</dt>
                            <dd>{{ number_format($bank->commission_rate * 100, 2) }}%</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Endpoint</dt>
                            <dd class="truncate">{{ $bank->api_endpoint }}</dd>
                        </div>
                    </dl>
                    @if($bank->settings)
                        <pre class="mt-4 max-h-32 overflow-auto rounded bg-black/5 p-2 text-xs text-zinc-600 dark:bg-white/10 dark:text-zinc-300">{{ json_encode($bank->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    @endif
                </div>
            @empty
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Банки не найдены.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>


