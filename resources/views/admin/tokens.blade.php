@php($title = 'Администратор: токены')
<x-layouts.app :title="$title">
    <!-- |KB Управление токенами: список и доступные действия -->
    <div class="space-y-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <a href="{{ route('admin.tokens.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Добавить токен</a>
        </div>

        @if(session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if(session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($tokens as $token)
                <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $token->name }}</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $token->symbol }}</p>
                        </div>
                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $token->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' }}">
                            {{ $token->is_active ? 'Активен' : 'Отключен' }}
                        </span>
                    </div>
                    <dl class="mt-4 space-y-2 text-sm text-zinc-700 dark:text-zinc-200">
                        <div class="flex justify-between">
                            <dt>Цена</dt>
                            <dd>{{ number_format($token->current_price, 2, '.', ' ') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Доступно</dt>
                            <dd>{{ number_format($token->available_supply, 4, '.', ' ') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>В балансе пользователей</dt>
                            <dd>{{ number_format($token->userBalances->sum('balance'), 4, '.', ' ') }}</dd>
                        </div>
                    </dl>
                </div>
            @empty
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Токены не найдены.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>


