@props([
    'title',
    'actions' => null,
])
<!-- |KB Обёртка панели с заголовком и action-слотом -->
<div {{ $attributes->class('rounded-lg border border-zinc-200 bg-white shadow-sm overflow-hidden dark:border-zinc-700 dark:bg-zinc-900') }}>
    <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $title }}</h2>
        @if($actions)
            <div class="flex gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
    <div class="p-4 bg-white dark:bg-zinc-900">
        {{ $slot }}
    </div>
</div>


