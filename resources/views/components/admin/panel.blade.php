@props([
    'title',
    'actions' => null,
])
<!-- |KB Обёртка панели с заголовком и action-слотом -->
<div {{ $attributes->class('rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900') }}>
    <div class="flex items-center justify-between border-b border-zinc-200 p-4 dark:border-zinc-700">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $title }}</h2>
        @if($actions)
            <div class="flex items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
    <div class="p-4">
        {{ $slot }}
    </div>
</div>


