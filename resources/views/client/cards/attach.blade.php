@php($title = 'Привязка карты через 2can')
<x-layouts.app :title="$title">
    <!-- |KB Форма привязки карты через 2can -->
    <div class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-md rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-6 text-2xl font-semibold text-zinc-900 dark:text-white">Привязка карты через 2can</h2>

            @if(session('success'))
                <x-ui.flash :message="session('success')" type="success" class="mb-4" />
            @endif
            @if(session('error'))
                <x-ui.flash :message="session('error')" type="error" class="mb-4" />
            @endif

            <div class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                Данные карты передаются напрямую через API 2can и не сохраняются на сервере. После успешной привязки сохраняется только токен и маска карты.
            </div>

            <form action="{{ route('client.cards.attach.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="number" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Номер карты</label>
                    <input type="text" name="number" id="number" maxlength="19" pattern="[0-9 ]{16,19}" placeholder="0000 0000 0000 0000" required
                           class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="expiry" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">ММ/ГГ</label>
                        <input type="text" name="expiry" id="expiry" maxlength="5" pattern="[0-9]{2}/[0-9]{2}" placeholder="12/34" required
                               class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <div>
                        <label for="cvv" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">CVV</label>
                        <input type="password" name="cvv" id="cvv" maxlength="4" pattern="[0-9]{3,4}" required
                               class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                </div>
                <div>
                    <label for="holder_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Имя на карте</label>
                    <input type="text" name="holder_name" id="holder_name" maxlength="26" placeholder="IVAN IVANOV" required
                           class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                </div>
                <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Привязать карту</button>
            </form>
        </div>
    </div>
</x-layouts.app>
