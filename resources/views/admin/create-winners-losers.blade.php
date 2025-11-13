@php($title = 'Создать списки победителей и проигравших')
<x-layouts.app :title="$title">
    <!-- |KB Форма для массового создания победителей и проигравших -->
    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <a href="{{ route('admin.winners-losers') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Назад</a>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <form action="{{ route('admin.winners-losers.store') }}" method="POST" class="space-y-6" id="winners-losers-form">
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Победители</label>
                            <button type="button" class="text-sm text-indigo-600 hover:underline" data-add-winner>Добавить</button>
                        </div>
                        <div class="space-y-3" data-winners-container>
                            <div class="rounded-md border border-zinc-200 p-3 dark:border-zinc-700" data-winner-entry>
                                <div class="flex justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                    <span>Победитель 1</span>
                                    <button type="button" class="text-red-500 hover:underline" data-remove-entry>Удалить</button>
                                </div>
                                <div class="mt-2 space-y-2">
                                    <input type="number" name="winners[0][user_id]" placeholder="ID пользователя" class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                                    <input type="number" step="0.00000001" name="winners[0][amount]" placeholder="Сумма" class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                                    <input type="number" name="winners[0][token_id]" placeholder="ID токена" class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                                </div>
                            </div>
                        </div>
                        @error('winners')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Проигравшие</label>
                            <button type="button" class="text-sm text-indigo-600 hover:underline" data-add-loser>Добавить</button>
                        </div>
                        <div class="space-y-3" data-losers-container>
                            <div class="rounded-md border border-zinc-200 p-3 dark:border-zinc-700" data-loser-entry>
                                <div class="flex justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                    <span>Проигравший 1</span>
                                    <button type="button" class="text-red-500 hover:underline" data-remove-entry>Удалить</button>
                                </div>
                                <div class="mt-2 space-y-2">
                                    <input type="number" name="losers[0][user_id]" placeholder="ID пользователя" class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                                    <input type="number" step="0.00000001" name="losers[0][amount]" placeholder="Сумма" class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                                    <input type="number" name="losers[0][token_id]" placeholder="ID токена" class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                                </div>
                            </div>
                        </div>
                        @error('losers')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.winners-losers') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Отмена</a>
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Создать</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('winners-losers-form');
            if (!form) return;

            const winnersContainer = form.querySelector('[data-winners-container]');
            const losersContainer = form.querySelector('[data-losers-container]');
            let winnerIndex = winnersContainer.querySelectorAll('[data-winner-entry]').length;
            let loserIndex = losersContainer.querySelectorAll('[data-loser-entry]').length;

            const buildEntry = (type, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'rounded-md border border-zinc-200 p-3 dark:border-zinc-700';
                wrapper.setAttribute(`data-${type}-entry`, '');
                wrapper.innerHTML = `
                    <div class="flex justify-between text-xs text-zinc-500 dark:text-zinc-400">
                        <span>${type === 'winner' ? 'Победитель' : 'Проигравший'} ${index + 1}</span>
                        <button type="button" class="text-red-500 hover:underline" data-remove-entry>Удалить</button>
                    </div>
                    <div class="mt-2 space-y-2">
                        <input type="number" name="${type === 'winner' ? 'winners' : 'losers'}[${index}][user_id]" placeholder="ID пользователя" class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                        <input type="number" step="0.00000001" name="${type === 'winner' ? 'winners' : 'losers'}[${index}][amount]" placeholder="Сумма" class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                        <input type="number" name="${type === 'winner' ? 'winners' : 'losers'}[${index}][token_id]" placeholder="ID токена" class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                    </div>
                `;
                return wrapper;
            };

            form.querySelector('[data-add-winner]').addEventListener('click', () => {
                const entry = buildEntry('winner', winnerIndex);
                winnersContainer.appendChild(entry);
                winnerIndex++;
            });

            form.querySelector('[data-add-loser]').addEventListener('click', () => {
                const entry = buildEntry('loser', loserIndex);
                losersContainer.appendChild(entry);
                loserIndex++;
            });

            form.addEventListener('click', (event) => {
                const removeBtn = event.target.closest('[data-remove-entry]');
                if (!removeBtn) return;

                const entry = removeBtn.closest('[data-winner-entry], [data-loser-entry]');
                if (entry) {
                    const container = entry.parentElement;
                    if (container.children.length > 1) {
                        entry.remove();
                        const items = container.children;
                        Array.from(items).forEach((item, idx) => {
                            const type = item.hasAttribute('data-winner-entry') ? 'winners' : 'losers';
                            item.querySelector('span').textContent = `${type === 'winners' ? 'Победитель' : 'Проигравший'} ${idx + 1}`;
                            item.querySelectorAll('input').forEach((input) => {
                                const name = input.getAttribute('name');
                                const updated = name.replace(/\[\d+\]/, `[${idx}]`);
                                input.setAttribute('name', updated);
                            });
                        });
                        if (container === winnersContainer) {
                            winnerIndex = items.length;
                        } else {
                            loserIndex = items.length;
                        }
                    }
                }
            });
        });
    </script>
@endpush


