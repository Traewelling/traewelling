<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="alert my-3 alert-danger alert-dismissible" role="alert">
                        {!! $error !!}
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            @endif

            @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $sessionKey => $cssKey)
                @if ($message = session()->get($sessionKey))
                    <div class="alert alert-{{$cssKey}} alert-dismissible">
                        <strong>{!! $message !!}</strong>
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            @endforeach

            @if($message = session()->get('mail-prompt'))
                <div class="alert alert-info alert-dismissible">
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
                    <strong>{!! $message !!}</strong>
                    <button class="btn btn-default" href="{{ route('verification.resend') }}"
                            onclick="event.preventDefault(); document.getElementById('resend-mail-form').submit();">
                        {{ __('controller.status.email-resend-mail') }}
                    </button>

                    <form id="resend-mail-form" action="{{ route('verification.resend') }}" method="POST"
                          style="display: none;">
                        @csrf
                    </form>
                </div>
            @endif

            @if(Session::has('message'))
                <div class="alert my-3 alert-info alert-dismissible" role="alert">
                    {!! Session::get('message') !!}
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @include('includes.messages.checkin-success')

            <div id="alert_placeholder"></div>
        </div>
    </div>
</div>
