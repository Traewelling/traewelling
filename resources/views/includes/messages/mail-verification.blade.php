@if(auth()->check() && isset(auth()->user()->email) && auth()->user()?->email_verified_at === null)
    <div class="alert alert-warning text-trwl">

        <form action="{{ route('verification.resend') }}" method="POST" class="float-end">
            @csrf

            <button class="btn btn-sm btn-primary" type="submit">
                <i class="fas fa-mouse-pointer me-2"></i>
                {{ __('controller.status.email-resend-mail') }}
            </button>
        </form>

        <h3>{{__('welcome')}} <i class="far fa-heart"></i></h3>
        <strong>{{__('email.verification.required')}} {{__('email.verification.btn')}}</strong>
    </div>
@endif
