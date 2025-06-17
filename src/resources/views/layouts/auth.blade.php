<!doctype html>
<html dir="ltr" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | {{ config('app.name') }}</title>

    {{-- Styles --}}
    <link href="/assets/css/auth-style.css" rel="stylesheet">
{{--    @vite(['resources/assets/scss/auth/auth-style.scss'])--}}
    @stack('styles')


</head>
<body>
<main class="main-box">
    <div class="main-box-content">
        @yield('content')
    </div>
</main>

{{--@vite(['resources/assets/js/pages/auth/auth-script.js'])--}}
<script src="/assets/js/auth-script.js"></script>
</body>
</html>
