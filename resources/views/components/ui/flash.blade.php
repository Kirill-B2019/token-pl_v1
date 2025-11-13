@props([
    'message',
    'type' => 'success',
])
<!-- |KB Всплывающее уведомление для отображения flash-сообщений -->
@php
    $color = [
        'success' => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/40 dark:text-green-200 dark:border-green-800',
        'error' => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/40 dark:text-red-200 dark:border-red-800',
        'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/40 dark:text-yellow-200 dark:border-yellow-800',
        'info' => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/40 dark:text-blue-200 dark:border-blue-800',
    ][$type] ?? 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/40 dark:text-blue-200 dark:border-blue-800';
@endphp
<div {{ $attributes->class("border rounded-md px-4 py-3 text-sm {$color}") }}>
    {{ $message }}
</div>


