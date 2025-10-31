@php($title = 'Назначение пользователей в группу')
<x-layouts.app :title="$title">
    <!-- |KB Интерфейс назначения пользователей к выбранной группе -->
    <div class="p-6 space-y-4 max-w-4xl">
        <h1 class="text-xl font-semibold">{{ $title }}: {{ $group->name }}</h1>

        @if(session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <form action="{{ route('admin.user-groups.assign.store', $group) }}" method="POST" class="space-y-4">
            @csrf
            <div class="overflow-x-auto bg-white dark:bg-zinc-900 rounded border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th class="p-3">В группе</th>
                            <th class="p-3">Пользователь</th>
                            <th class="p-3">Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                <td class="p-3">
                                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" {{ in_array($user->id, $userIdsInGroup) ? 'checked' : '' }}>
                                </td>
                                <td class="p-3">{{ $user->name }}</td>
                                <td class="p-3">{{ $user->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.user-groups.index') }}" class="px-3 py-2 border rounded">Назад</a>
                <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Сохранить состав</button>
            </div>
        </form>
    </div>
</x-layouts.app>


