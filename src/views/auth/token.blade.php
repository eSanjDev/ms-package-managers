<!doctype html>
<html dir="{{ app()->getLocale() == 'fa' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{__("manager::manager.title")}} | {{ config('app.name') }}</title>

    {{-- Styles --}}
    <link href="{{asset("/assets/vendor/manager/css/auth-style.css")}}" rel="stylesheet">

</head>
<body>
<main class="main-box">
    <div class="main-box-content">
        <div class="form-box">
            <section class="form-box-content">
                <header class="logo-box">
                    <img src="{{ config('esanj.manager.logo_path')??asset('assets/vendor/manager/img/logo.png') }}"
                         alt="{{ trans('app.logo_alt') }}">
                </header>

                <div class="form-body">
                    <div class="form-box-main">
                        <h1>{{ trans('manager::manager.welcome_back') }}</h1>
                        <p>{{ trans('manager::manager.steps.security.box_sub_title') }}</p>

                        <form method="post" action="{{route("managers.auth.login")}}">
                            @csrf
                            <div class="input-box @error('token') error @enderror">
                                <p>{{ trans('manager::manager.labels.token') }}</p>
                                <div class="form-input">
                                    <input type="password" name="token" required
                                           placeholder="{{ trans('manager::manager.steps.security.token_placeholder') }}">
                                    <i onclick="AuthScripts.showHidePass(this)" class="icon-password"></i>
                                </div>
                                <span data-message="{{$errors->first('token')}}"></span>
                            </div>

                            <button type="submit" class="btn-submit-form">
                                {{ trans('manager::manager.confirm') }}
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </div>

    </div>
</main>

<script src="{{asset("/assets/vendor/manager/js/auth-script.js")}}"></script>
</body>
</html>
