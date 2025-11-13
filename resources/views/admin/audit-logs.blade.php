@php($title = 'Журнал действий')
<x-layouts.app :title="$title">
    <!-- |KB Журнал аудита с фильтром по событиям и сущностям -->
    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
        </div>

        <div class="overflow-x-auto rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        <th class="px-4 py-3">Дата</th>
                        <th class="px-4 py-3">Событие</th>
                        <th class="px-4 py-3">Пользователь</th>
                        <th class="px-4 py-3">Сущность</th>
                        <th class="px-4 py-3">IP</th>
                        <th class="px-4 py-3">Данные</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($auditLogs as $log)
                        <tr class="align-top text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-4 py-3">{{ strtoupper($log->event) }}</td>
                            <td class="px-4 py-3">{{ $log->user->name ?? 'Система' }}</td>
                            <td class="px-4 py-3">{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                            <td class="px-4 py-3">{{ $log->ip_address }}</td>
                            <td class="px-4 py-3">
                                @if($log->metadata)
                                    <pre class="max-w-xs overflow-x-auto rounded bg-black/5 p-2 text-xs text-zinc-600 dark:bg-white/10 dark:text-zinc-300">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">Записей аудита нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $auditLogs->links() }}
        </div>
    </div>
</x-layouts.app>


