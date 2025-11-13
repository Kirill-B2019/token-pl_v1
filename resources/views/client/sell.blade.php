@php($title = 'Продажа токенов')
<x-layouts.app :title="$title">
    <!-- |KB Форма продажи токенов -->
    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <a href="{{ route('client.dashboard') }}" class="text-sm text-indigo-600 hover:underline">Назад к кабинету</a>
        </div>

        @if(session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if(session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <form action="{{ route('client.sell.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Выберите токен</label>
                    <select name="token_id" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                        <option value="">Выберите...</option>
                        @foreach($balances as $balance)
                            <option value="{{ $balance->token->id }}">{{ $balance->token->name }} ({{ number_format($balance->available_balance, 6, '.', ' ') }} доступно)</option>
                        @endforeach
                    </select>
                    @error('token_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Количество</label>
                    <input type="number" step="0.00000001" name="amount" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                    @error('amount')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('client.dashboard') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Отмена</a>
                    <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500">Продать</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>


