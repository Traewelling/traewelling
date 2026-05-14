<form action="{{ route('login') }}" method="POST" class="flex flex-col gap-4">
    @csrf
    <h1 class="text-3xl font-bold self-center">{{ __('user.login') }}</h1>

    @if(config('app.registration.enabled'))
        <span class="self-center">
            {{ __('user.no-account') }}
            <a class="link link-accent" href="{{ route('register') }}">{{ __('user.register') }}</a>
        </span>
    @endif

    <button class="btn btn-neutral" onclick="mastodon_modal.showModal()" type="button">
        <i class="fa-brands fa-mastodon text-primary"></i>
        {{ __('user.login.mastodon') }}
    </button>

    <div class="divider">{{ __('user.login.or') }}</div>

    <fieldset class="fieldset">
        <div class="label">
            <span class="label-text">{{ __('user.login-credentials') }}</span>
        </div>

        <input type="text" class="input w-full input-bordered" id="login" name="login"
               required autocomplete="username" autocapitalize="none" autofocus
        />
    </fieldset>

    <fieldset class="fieldset">
        <div class="label">
            <span class="label-text">{{ __('user.password') }}</span>
        </div>

        <input type="password" id="password" name="password" class="input w-full input-bordered"
               required autocomplete="current-password"
        />

        <a class="label-text link link-secondary self-end" href="{{ route('password.request') }}">
            {{ __('user.forgot-password') }}
        </a>
    </fieldset>

    <div class="fieldset">
        <label class="cursor-pointer label self-start gap-2">
            <input type="checkbox" class="checkbox" id="remember" name="remember"/>
            <span class="label-text">{{ __('user.remember-me') }}</span>
        </label>
    </div>

    <button class="btn btn-primary">{{ __('user.login') }}</button>
</form>
