@php($title = 'Победители и проигравшие')
<x-layouts.app :title="$title">
    <!-- |KB Управление списками победителей и проигравших -->
    <div class="space-y-6 p-6">
        @php($statusLabels = [
            'pending' => 'В ожидании',
            'processed' => 'Обработано',
            'paid' => 'Выплачено',
        ])

         <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <a href="{{ route('admin.winners-losers.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Создать список</a>
        </div>

        @if(session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if(session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

        <div class="overflow-x-auto rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        <th class="px-4 py-3">Дата</th>
                        <th class="px-4 py-3">Пользователь</th>
                        <th class="px-4 py-3">Тип</th>
                        <th class="px-4 py-3">Токен</th>
                        <th class="px-4 py-3">Сумма</th>
                        <th class="px-4 py-3">Статус</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($winnersLosers as $record)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3">{{ $record->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $record->user->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $record->type === 'winner' ? 'Победитель' : 'Проигравший' }}</td>
                            <td class="px-4 py-3">{{ $record->token->symbol ?? '—' }}</td>
                            <td class="px-4 py-3">{{ number_format($record->amount, 2, '.', ' ') }}</td>
                            <td class="px-4 py-3">
                                 <span class="rounded-full px-2 py-1 text-xs font-medium {{ $record->status === 'processed' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200' }}">
                                     {{ $statusLabels[$record->status] ?? ucfirst($record->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($record->status === 'pending')
                                    <form action="{{ route('admin.winners-losers.process', $record) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-sm text-indigo-600 hover:underline">Отметить обработанной</button>
                                    </form>
                                @else
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Обработано</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">Записей нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $winnersLosers->links() }}
        </div>
    </div>
</x-layouts.app>


