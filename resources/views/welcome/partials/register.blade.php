<form action="{{ route('register') }}" method="POST" class="flex flex-col gap-4">
    @csrf
    <h1 class="text-3xl font-bold self-center">{{ __('user.register') }}</h1>

    <span class="self-center">
            {{ __('user.has-account') }}
            <a class="link link-accent" href="{{ route('login') }}">{{ __('user.login') }}</a>
        </span>

    <button class="btn btn-neutral" onclick="mastodon_modal.showModal()" type="button">
        <figure class="w-4 fill-white">
            @include('welcome.partials.mastodon-icon')
        </figure>
        {{ __('user.login.mastodon') }}
    </button>

    <div class="divider">{{ __('user.login.or') }}</div>

    <fieldset class="fieldset">
        <div class="label">
            <span class="label-text">{{ __('user.username') }}</span>
        </div>

        <label class="input validator w-full @error('username') input-error @enderror">
            @
            <input type="text" class="grow" id="username" name="username"
                   required autocomplete="username" autocapitalize="none" autofocus
            />
        </label>
        @error('username')
        <p class="validator-hint text-error visible">
            {{ $message }}
        </p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <div class="label">
            <span class="label-text">{{ __('user.displayname') }}</span>
        </div>

        <input type="text" class="input validator w-full @error('name') input-error @enderror" id="name" name="name"
               required
               autocomplete="name"/>
        @error('name')
        <p class="validator-hint text-error visible">
            {{ $message }}
        </p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <div class="label">
            <span class="label-text">{{ __('user.email') }}</span>
        </div>

        <input type="text" class="input validator w-full @error('email') input-error @enderror" id="email" name="email"
               required
               value="{{ old('email') }}" autocomplete="email"/>
        @error('email')
        <p class="validator-hint text-error visible">
            {{ $message }}
        </p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <div class="label">
            <span class="label-text">{{ __('user.password') }}</span>
        </div>

        <input type="password" class="input validator w-full @error('password') input-error @enderror" id="password"
               name="password" required
               value="{{ old('email') }}" autocomplete="new-password" minlength="8"/>
        @error('password')
        <p class="validator-hint text-error visible">
            {{ $message }}
        </p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <div class="label">
            <span class="label-text">{{ __('settings.confirm-password') }}</span>
        </div>

        <input type="password" class="input w-full" id="password-confirm" name="password_confirmation" required
               value="{{ old('email') }}" autocomplete="new-password" minlength="8"/>
    </fieldset>

    <button class="btn btn-primary">{{ __('user.register') }}</button>
</form>
