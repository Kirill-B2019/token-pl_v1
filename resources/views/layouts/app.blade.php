@php
    $pageTitle = trim($__env->yieldContent('title'));
    $pageTitle = $pageTitle !== '' ? $pageTitle : __('Личный кабинет');
@endphp

<x-layouts.app :title="$pageTitle">
    @yield('content')
</x-layouts.app>


