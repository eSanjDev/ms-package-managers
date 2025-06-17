<div class="form-box">
    <section class="form-box-content">
        <header class="logo-box">
            <img src="{{ asset('assets/img/tmp/tmp-full-logo.png') }}" alt="{{ trans('app.logo_alt') }}">
        </header>

        <div class="form-body">
            <div class="form-box-main">
                <h1>{{ trans('auth.welcome_back') }}</h1>
                <p>{{ trans('auth.steps.security.box_sub_title') }}</p>

                <form wire:submit.prevent="submit" novalidate>
                    <div class="input-box @error('token') error @enderror">
                        <p>{{ trans('auth.labels.password') }}</p>
                        <div class="form-input">
                            <input type="password" wire:model="token"
                                   placeholder="{{ trans('auth.steps.security.token_placeholder') }}">
                            <i onclick="AuthScripts.showHidePass(this)" class="icon-password"></i>
                        </div>
                        <span data-message="{{$errors->first('token')}}"></span>
                    </div>

                    <button type="submit" class="btn-submit-form">
                        {{ trans('auth.confirm') }}
                    </button>
                </form>
            </div>
        </div>
    </section>
</div>
