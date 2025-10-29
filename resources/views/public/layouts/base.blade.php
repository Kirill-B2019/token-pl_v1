<!-- |KB Базовый макет публичной части: подключение стилей, фона, навигации и футера -->
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CARDFLY-CRYPTO — Токен-платформа</title>
    <link href="{{ asset('landing/style.css') }}" rel="stylesheet">
</head>
<body id="top">
    @include('public.partials.background')
    @include('public.partials.nav')
    <main>
        @yield('content')
    </main>
    @include('public.partials.footer')
    <script src="{{ asset('landing/scripts.js') }}"></script>
</body>
</html>


