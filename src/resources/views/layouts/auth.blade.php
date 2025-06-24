<!doctype html>
<html dir="ltr" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | {{ config('app.name') }}</title>

    {{-- Styles --}}
    <link href="{{asset("/assets/vendor/manager/css/auth-style.css")}}" rel="stylesheet">

</head>
<body>
<main class="main-box">
    <div class="main-box-content">
        @yield('content')
    </div>
</main>

<script src="{{asset("/assets/vendor/manager/js/auth-script.js")}}"></script>
</body>
</html>
