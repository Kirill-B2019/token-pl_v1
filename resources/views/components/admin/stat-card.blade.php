@props([
    'title',
    'value',
    'subtitle' => null,
])
<!-- |KB Карточка статистики для админского дашборда -->
<div {{ $attributes->class('rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900') }}>
    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $title }}</p>
    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ $value }}</p>
    @if($subtitle)
        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</p>
    @endif
</div>


