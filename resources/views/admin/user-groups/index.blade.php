@php($title = 'Группы пользователей')
<x-layouts.app :title="$title">
    <!-- |KB Список групп пользователей -->
    <div class="space-y-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <a href="{{ route('admin.user-groups.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Создать группу</a>
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
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Название</th>
                        <th class="px-4 py-3">Код</th>
                        <th class="px-4 py-3">Активна</th>
                        <th class="px-4 py-3">Участники</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($groups as $group)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3">{{ $group->id }}</td>
                            <td class="px-4 py-3">{{ $group->name }}</td>
                            <td class="px-4 py-3">{{ $group->code }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-1 text-xs font-medium {{ $group->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' }}">
                                    {{ $group->is_active ? 'Активна' : 'Неактивна' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $group->users()->count() }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.user-groups.edit', $group) }}" class="text-indigo-600 hover:underline">Редактировать</a>
                                    <a href="{{ route('admin.user-groups.assign', $group) }}" class="text-indigo-600 hover:underline">Назначить</a>
                                    <form action="{{ route('admin.user-groups.destroy', $group) }}" method="POST" class="inline" onsubmit="return confirm('Удалить группу?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">Группы не найдены.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $groups->links() }}
        </div>
    </div>
</x-layouts.app>


