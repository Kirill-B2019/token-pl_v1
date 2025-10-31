@php($title = 'Создать группу')
<x-layouts.app :title="$title">
    <!-- |KB Форма создания группы пользователей -->
    <div class="p-6 space-y-4 max-w-2xl">
        <h1 class="text-xl font-semibold">{{ $title }}</h1>

        <form action="{{ route('admin.user-groups.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm mb-1">Название</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border p-2 rounded" required>
                @error('name')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1">Код</label>
                <input type="text" name="code" value="{{ old('code') }}" class="w-full border p-2 rounded" required>
                @error('code')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1">Описание</label>
                <textarea name="description" class="w-full border p-2 rounded" rows="4">{{ old('description') }}</textarea>
                @error('description')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" checked>
                <span>Активна</span>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.user-groups.index') }}" class="px-3 py-2 border rounded">Отмена</a>
                <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Создать</button>
            </div>
        </form>
    </div>
</x-layouts.app>


