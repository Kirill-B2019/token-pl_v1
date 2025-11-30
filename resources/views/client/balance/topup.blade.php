@php($title = 'Пополнение баланса')
<x-layouts.app :title="$title">
    <!-- |KB Форма пополнения баланса через 2can -->
    <div class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-lg rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white">Пополнение баланса через 2can</h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    Текущий баланс: <span class="font-semibold text-green-600">{{ auth()->user()->formatted_rub_balance }}</span>
                </p>
            </div>

            @if(session('success'))
                <x-ui.flash :message="session('success')" type="success" class="mb-4" />
            @endif
            @if(session('error'))
                <x-ui.flash :message="session('error')" type="error" class="mb-4" />
            @endif

            <form action="{{ route('client.balance.topup.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="amount" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        Сумма пополнения (₽)
                    </label>
                    <input type="number"
                           name="amount"
                           id="amount"
                           min="{{ $minAmount ?? 10 }}"
                           max="{{ $maxAmount ?? 50000 }}"
                           step="0.01"
                           required
                           placeholder="Введите сумму"
                           class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Минимальная сумма: {{ number_format($minAmount ?? 10, 0, '.', ' ') }} ₽,
                        Максимальная сумма: {{ number_format($maxAmount ?? 50000, 0, '.', ' ') }} ₽
                    </p>
                </div>

                <div>
                    <label for="card_token" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Выберите карту для оплаты</label>
                    <select name="card_token" id="card_token"
                            class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Выберите карту —</option>
                        @php($userCards = auth()->user()->activeCards)
                        @forelse($userCards as $card)
                            <option value="{{ $card->twocan_card_token }}" {{ $card->is_default ? 'selected' : '' }}>
                                {{ $card->card_mask }}
                                @if($card->card_brand) ({{ $card->card_brand }}) @endif
                                @if($card->is_default) - По умолчанию @endif
                            </option>
                        @empty
                            <option value="" disabled>У вас нет привязанных карт</option>
                        @endforelse
                    </select>
                    <div class="mt-2">
                        <a href="{{ route('client.cards.attach') }}" class="text-sm text-indigo-600 hover:underline dark:text-indigo-400">
                            + Привязать новую карту
                        </a>
                        @if($userCards->isNotEmpty())
                            <span class="text-zinc-400 dark:text-zinc-600"> | </span>
                            <a href="{{ route('client.cards.index') }}" class="text-sm text-zinc-600 hover:underline dark:text-zinc-400">
                                Управление картами
                            </a>
                        @endif
                    </div>
                </div>
                <div>
                    <label for="card_token" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Выберите карту для списания</label>
                    <select name="card_token" id="card_token"
                            class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <option value="">— Выберите карту —</option>
                    </select>
                    <div class="mt-2">
                        <a href="{{ route('client.cards.attach') }}" class="text-sm text-indigo-600 hover:underline">+ Привязать новую карту</a>
                    </div>
                </div>
                <button type="submit" class="w-full rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-500">Пополнить</button>
            </form>
        </div>
    </div>
</x-layouts.app>
