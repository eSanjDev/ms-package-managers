<div class="form-box">
    <section class="form-box-content">
        <header class="logo-box">
            <img src="{{ config('manager.logo_path')??asset('assets/vendor/manager/img/logo.png') }}"
                 alt="{{ trans('app.logo_alt') }}">
        </header>

        <div class="form-body">
            <div class="form-box-main">
                <h1>{{ trans('manager::manager.welcome_back') }}</h1>
                <p>{{ trans('manager::manager.steps.security.box_sub_title') }}</p>

                <form wire:submit.prevent="submit" novalidate>
                    <div class="input-box @error('token') error @enderror">
                        <p>{{ trans('manager::manager.labels.token') }}</p>
                        <div class="form-input">
                            <input type="password" wire:model="token"
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
