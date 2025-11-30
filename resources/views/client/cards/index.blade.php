@php($title = 'Мои карты')
<x-layouts.app :title="$title">
    <!-- |KB Список привязанных карт пользователя -->
    <div class="space-y-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <a href="{{ route('client.cards.attach') }}" class="rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-500">
                + Привязать карту
            </a>
        </div>

        @if(session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if(session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

        <x-admin.panel title="Привязанные карты">
            @if($cards->isNotEmpty())
                <div class="space-y-4">
                    @foreach($cards as $card)
                        <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex items-center gap-4">
                                <!-- Card Icon -->
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                </div>

                                <!-- Card Info -->
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $card->card_mask }}</span>
                                        @if($card->card_brand)
                                            <span class="rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">{{ $card->card_brand }}</span>
                                        @endif
                                        @if($card->is_default)
                                            <span class="rounded bg-green-100 px-2 py-1 text-xs text-green-800 dark:bg-green-900/40 dark:text-green-200">По умолчанию</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                        @if($card->card_holder_name)
                                            {{ $card->card_holder_name }} •
                                        @endif
                                        Истекает {{ $card->formatted_expiry }}
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                @if(!$card->is_default)
                                    <form action="{{ route('client.cards.set-default', $card) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20">
                                            Сделать по умолчанию
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('client.cards.delete', $card) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Вы уверены, что хотите удалить эту карту?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded px-3 py-1 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">Нет привязанных карт</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Привяжите карту для быстрого пополнения баланса</p>
                    <div class="mt-6">
                        <a href="{{ route('client.cards.attach') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500">
                            Привязать первую карту
                        </a>
                    </div>
                </div>
            @endif
        </x-admin.panel>

        <!-- Quick Actions -->
        <div class="flex gap-4">
            <a href="{{ route('client.balance.topup') }}" class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-500">
                Пополнить баланс
            </a>
            <a href="{{ route('client.dashboard') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800">
                Вернуться в кабинет
            </a>
        </div>
    </div>
</x-layouts.app>
