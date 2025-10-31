@php($title = 'Редактировать группу')
<x-layouts.app :title="$title">
    <!-- |KB Форма редактирования группы пользователей -->
    <div class="p-6 space-y-4 max-w-2xl">
        <h1 class="text-xl font-semibold">{{ $title }}</h1>

        <form action="{{ route('admin.user-groups.update', $group) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm mb-1">Название</label>
                <input type="text" name="name" value="{{ old('name', $group->name) }}" class="w-full border p-2 rounded" required>
                @error('name')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1">Код</label>
                <input type="text" name="code" value="{{ old('code', $group->code) }}" class="w-full border p-2 rounded" required>
                @error('code')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1">Описание</label>
                <textarea name="description" class="w-full border p-2 rounded" rows="4">{{ old('description', $group->description) }}</textarea>
                @error('description')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $group->is_active) ? 'checked' : '' }}>
                <span>Активна</span>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.user-groups.index') }}" class="px-3 py-2 border rounded">Назад</a>
                <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Сохранить</button>
            </div>
        </form>
    </div>
</x-layouts.app>


