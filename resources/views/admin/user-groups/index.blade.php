@php($title = 'Группы пользователей')
<x-layouts.app :title="$title">
    <!-- |KB Список групп пользователей -->
    <div class="p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ $title }}</h1>
            <a href="{{ route('admin.user-groups.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Создать группу</a>
        </div>

        @if(session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <div class="overflow-x-auto bg-white dark:bg-zinc-900 rounded border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left">
                        <th class="p-3">ID</th>
                        <th class="p-3">Название</th>
                        <th class="p-3">Код</th>
                        <th class="p-3">Активна</th>
                        <th class="p-3">Участники</th>
                        <th class="p-3">Действия</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($groups as $group)
                    <tr class="border-t border-zinc-200 dark:border-zinc-700">
                        <td class="p-3">{{ $group->id }}</td>
                        <td class="p-3">{{ $group->name }}</td>
                        <td class="p-3">{{ $group->code }}</td>
                        <td class="p-3">{{ $group->is_active ? 'Да' : 'Нет' }}</td>
                        <td class="p-3">{{ $group->users()->count() }}</td>
                        <td class="p-3 space-x-2">
                            <a href="{{ route('admin.user-groups.edit', $group) }}" class="text-blue-600">Редактировать</a>
                            <a href="{{ route('admin.user-groups.assign', $group) }}" class="text-indigo-600">Назначить</a>
                            <form action="{{ route('admin.user-groups.destroy', $group) }}" method="POST" class="inline" onsubmit="return confirm('Удалить группу?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div>
            {{ $groups->links() }}
        </div>
    </div>
</x-layouts.app>


