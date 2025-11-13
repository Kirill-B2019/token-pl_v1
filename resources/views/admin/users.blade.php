@php($title = 'Администратор: пользователи')
<x-layouts.app :title="$title">
    <!-- |KB Список пользователей с фильтрацией по активности -->
    <div class="space-y-6 p-6">
        @php($activeCount = \App\Models\User::where('is_active', true)->count())
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                Всего: {{ $users->total() }} • Активных: {{ $activeCount }}
            </div>
        </div>

        @if (session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if (session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

        <div class="overflow-x-auto rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        <th class="px-4 py-3">Пользователь</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Роль</th>
                        <th class="px-4 py-3">Статус</th>
                        <th class="px-4 py-3">Транзакций</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($users as $user)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $user->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">ID: {{ $user->id }} / {{ $user->unique_id }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $user->email }}</td>
                            <td class="px-4 py-3 capitalize">{{ __($user->role) }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-1 text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' }}">
                                    {{ $user->is_active ? 'Активен' : 'Заблокирован' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $user->transactions->count() }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:underline">Подробнее</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                Пользователи не найдены.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $users->links() }}
        </div>
    </div>
</x-layouts.app>


