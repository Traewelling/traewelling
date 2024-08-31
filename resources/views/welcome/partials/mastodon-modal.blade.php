<dialog id="mastodon_modal" class="modal modal-top sm:modal-middle">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 class="text-lg font-bold">{{ __('user.login.mastodon') }}</h3>

        <form method="GET" action="{{ url('/auth/redirect/mastodon') }}" class="mt-4 join w-full">
            <input type="text" placeholder="{{ __('user.mastodon-instance-url') }}" name="domain" required
                   class="input input-bordered w-full join-item"/>
            <button type="submit" class="btn btn-primary join-item">
                <i class="fab fa-mastodon"></i>
                {{ __('user.login') }}
            </button>
        </form>
    </div>
</dialog>
