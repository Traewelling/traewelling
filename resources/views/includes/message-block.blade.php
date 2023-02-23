<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="alert my-3 alert-danger alert-dismissible" role="alert">
                        {!! $error !!}
                        <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            @endif

            @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $sessionKey => $cssKey)
                @if ($message = session()->get($sessionKey))
                    <div class="alert alert-{{$cssKey}} alert-dismissible">
                        <strong>{!! $message !!}</strong>
                        <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            @endforeach

            @if(session()->has('message'))
                <div class="alert my-3 alert-info alert-dismissible" role="alert">
                    {!! session()->get('message') !!}
                    <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(!request()->routeIs('gdpr.intercept'))
                @include('includes.messages.mail-verification')
                @include('includes.messages.checkin-success')
            @endif
            <div id="alert_placeholder"></div>
        </div>
    </div>
</div>
