@php($title = 'Профиль пользователя #' . $user->id)
<x-layouts.app :title="$title">
    <!-- |KB Карточка пользователя с активностью и группами -->
    <div class="space-y-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $user->name }}</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $user->email }} • Роль: {{ $user->role }}</p>
            </div>
            <a href="{{ route('admin.users') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Назад к списку</a>
        </div>

        @if(session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if(session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <x-admin.panel title="Информация">
                <dl class="space-y-3 text-sm text-zinc-700 dark:text-zinc-200">
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">ID пользователя</dt>
                        <dd>{{ $user->id }} / {{ $user->unique_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Статус</dt>
                        <dd>
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' }}">
                                {{ $user->is_active ? 'Активен' : 'Заблокирован' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Телефон</dt>
                        <dd>{{ $user->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Последний вход</dt>
                        <dd>{{ optional($user->last_login_at)->format('d.m.Y H:i') ?? '—' }}</dd>
                    </div>
                </dl>
                <form action="{{ route('admin.users.status.update', $user) }}" method="POST" class="mt-4 space-y-2">
                    @csrf
                    <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                    <button type="submit" class="w-full rounded-md {{ $user->is_active ? 'bg-red-600 hover:bg-red-500' : 'bg-green-600 hover:bg-green-500' }} px-3 py-2 text-sm font-medium text-white">
                        {{ $user->is_active ? 'Заблокировать' : 'Разблокировать' }}
                    </button>
                </form>
            </x-admin.panel>

            <x-admin.panel title="Группы пользователя" class="lg:col-span-2">
                <div class="flex flex-wrap gap-2">
                    @forelse($user->userGroups as $group)
                        <span class="rounded-full bg-indigo-100 px-3 py-1 text-sm text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200">
                            {{ $group->name }} ({{ $group->code }})
                        </span>
                    @empty
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Пользователь не состоит ни в одной группе.</p>
                    @endforelse
                </div>
                <a href="{{ route('admin.user-groups.index') }}" class="mt-4 inline-block text-sm text-indigo-600 hover:underline">К списку групп</a>
            </x-admin.panel>
        </div>

        @php($typeLabels = [
            'buy' => 'Покупка',
            'sell' => 'Продажа',
            'transfer' => 'Перевод',
            'refund' => 'Возврат',
        ])
        @php($statusLabels = [
            'pending' => 'В ожидании',
            'processing' => 'В обработке',
            'completed' => 'Завершено',
            'failed' => 'Ошибка',
            'cancelled' => 'Отменено',
        ])

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
                        @forelse($user->transactions->take(10) as $transaction)
                            <tr class="text-zinc-800 dark:text-zinc-100">
                                <td class="px-3 py-2">{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-3 py-2">{{ $typeLabels[$transaction->type] ?? ucfirst($transaction->type) }}</td>
                                <td class="px-3 py-2">{{ $transaction->token->symbol ?? '—' }}</td>
                                <td class="px-3 py-2">{{ number_format($transaction->total_amount, 2, '.', ' ') }}</td>
                                <td class="px-3 py-2">{{ $statusLabels[$transaction->status] ?? ucfirst($transaction->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">Транзакций нет.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.panel>
    </div>
</x-layouts.app>


