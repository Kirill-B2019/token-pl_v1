@php
    $user = auth()->user();
    $role = $user->role;
@endphp
<x-layouts.app :title="__('Dashboard')">
    <!-- |KB Ролевой дашборд: быстрые ссылки и ключевые показатели -->
    <div class="space-y-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Здравствуйте, {{ $user->name }}</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Роль: {{ ucfirst($role) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($user->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Админ-панель</a>
                    <a href="{{ route('admin.users') }}" class="rounded-md border border-indigo-600 px-3 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white">Пользователи</a>
                    <a href="{{ route('admin.transactions') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Транзакции</a>
                @elseif($user->isBroker())
                    <a href="{{ route('broker.dashboard') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Дашборд брокера</a>
                    <a href="{{ route('broker.tokens') }}" class="rounded-md border border-indigo-600 px-3 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white">Токены</a>
                    <a href="{{ route('broker.reserves') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Резервы</a>
                @else
                    <a href="{{ route('client.dashboard') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Кабинет клиента</a>
                    <a href="{{ route('client.packages') }}" class="rounded-md border border-indigo-600 px-3 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white">Пакеты токенов</a>
                    <a href="{{ route('client.transactions') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">История</a>
                @endif
            </div>
        </div>

        @if($user->isAdmin())
            @php
                $adminStats = [
                    'users' => \App\Models\User::count(),
                    'transactions' => \App\Models\Transaction::count(),
                    'pending' => \App\Models\Transaction::where('status', 'pending')->count(),
                    'tokens' => \App\Models\Token::count(),
                    'groups' => \App\Models\UserGroup::count(),
                    'banks' => \App\Models\Bank::count(),
                ];
                $recentTransactions = \App\Models\Transaction::with(['user', 'token'])->latest()->limit(5)->get();
            @endphp
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <x-admin.stat-card title="Пользователи" :value="$adminStats['users']" />
                <x-admin.stat-card title="Транзакции" :value="$adminStats['transactions']" :subtitle="'Ожидают: ' . $adminStats['pending']" />
                <x-admin.stat-card title="Токены" :value="$adminStats['tokens']" />
                <x-admin.stat-card title="Группы" :value="$adminStats['groups']" />
                <x-admin.stat-card title="Банки" :value="$adminStats['banks']" />
            </div>
            <x-admin.panel title="Последние транзакции">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <th class="px-3 py-2">Дата</th>
                                <th class="px-3 py-2">Пользователь</th>
                                <th class="px-3 py-2">Тип</th>
                                <th class="px-3 py-2">Статус</th>
                                <th class="px-3 py-2">Сумма</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($recentTransactions as $transaction)
                                <tr class="text-zinc-800 dark:text-zinc-100">
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-3 py-2">{{ $transaction->user->name ?? '—' }}</td>
                                    <td class="px-3 py-2 capitalize">{{ __($transaction->type) }}</td>
                                    <td class="px-3 py-2">{{ $transaction->status }}</td>
                                    <td class="px-3 py-2">{{ number_format($transaction->total_amount, 2, '.', ' ') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">Нет транзакций.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.panel>
        @elseif($user->isBroker())
            @php
                $brokerPending = \App\Models\Transaction::where('status', 'pending')->count();
                $tokenCount = \App\Models\Token::count();
                $lowReserves = \App\Models\Token::where('available_supply', '<', 100)->count();
                $pendingTransactions = \App\Models\Transaction::with(['user','token'])->where('status','pending')->latest()->limit(5)->get();
            @endphp
            <div class="grid gap-4 md:grid-cols-3">
                <x-admin.stat-card title="Ожидают обработки" :value="$brokerPending" />
                <x-admin.stat-card title="Токены в системе" :value="$tokenCount" />
                <x-admin.stat-card title="Низкий резерв" :value="$lowReserves" />
            </div>
            <x-admin.panel title="Последние заявки клиентов">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <th class="px-3 py-2">Дата</th>
                                <th class="px-3 py-2">Пользователь</th>
                                <th class="px-3 py-2">Тип</th>
                                <th class="px-3 py-2">Сумма</th>
                                <th class="px-3 py-2">Токен</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($pendingTransactions as $transaction)
                                <tr class="text-zinc-800 dark:text-zinc-100">
                                    <td class="px-3 py-2">{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-3 py-2">{{ $transaction->user->name ?? '—' }}</td>
                                    <td class="px-3 py-2 capitalize">{{ __($transaction->type) }}</td>
                                    <td class="px-3 py-2">{{ number_format($transaction->total_amount, 2, '.', ' ') }}</td>
                                    <td class="px-3 py-2">{{ $transaction->token->symbol ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">Нет ожидающих заявок.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.panel>
        @else
            @php
                $balances = \App\Models\UserBalance::with('token')->where('user_id', $user->id)->get();
                $recentTransactions = \App\Models\Transaction::with('token')->where('user_id', $user->id)->latest()->limit(5)->get();
            @endphp
            <x-admin.panel title="Баланс токенов">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($balances as $balance)
                        <div class="rounded-md border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $balance->token->name ?? '—' }}</h3>
                            <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ number_format($balance->balance, 6, '.', ' ') }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Заблокировано: {{ number_format($balance->locked_balance, 6, '.', ' ') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">У вас ещё нет токенов.</p>
                    @endforelse
                </div>
            </x-admin.panel>
            <x-admin.panel title="Последние транзакции">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <th class="px-3 py-2">Дата</th>
                                <th class="px-3 py-2">Тип</th>
                                <th class="px-3 py-2">Токен</th>
                                <th class="px-3 py-2">Сумма</th>
                                <th class="px-3 py-2">Статус</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($recentTransactions as $transaction)
                                <tr class="text-zinc-800 dark:text-zinc-100">
                                    <td class="px-3 py-2">{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-3 py-2 capitalize">{{ __($transaction->type) }}</td>
                                    <td class="px-3 py-2">{{ $transaction->token->symbol ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ number_format($transaction->total_amount, 2, '.', ' ') }}</td>
                                    <td class="px-3 py-2">{{ $transaction->status }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">Нет транзакций.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.panel>
        @endif
    </div>
</x-layouts.app>
