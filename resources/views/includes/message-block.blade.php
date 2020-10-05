<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert my-3 alert-danger" role="alert">
                {!! $error !!}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endforeach
    @endif

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-block">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>{!! $message !!}</strong>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-block">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>{!! $message !!}</strong>
        </div>
    @endif


    @if ($message = Session::get('warning'))
        <div class="alert alert-warning alert-block">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>{!! $message !!}</strong>
        </div>
    @endif


    @if ($message = Session::get('info'))
        <div class="alert alert-info alert-block">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>{!! $message !!}</strong>
        </div>
    @endif

    @if($message = Session::get('mail-prompt'))
            <div class="alert alert-info alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
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
        <div class="alert my-3 alert-info" role="alert">
            {!! Session::get('message') !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(Session::has('checkin-success'))
        <div class="alert alert-success" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="alert-heading">{{ __('controller.transport.checkin-heading') }}</h4>
            <p>{{ trans_choice('controller.transport.checkin-ok', preg_match('/\s/', Session::get('checkin-success')['lineName']), ['lineName' => Session::get('checkin-success')['lineName']]) }}</p>
            @if(Session::get('checkin-success')['alsoOnThisConnection']->count() >= 1)
                <p>{{ __('controller.transport.also-in-connection') }}</p>
                <ul>
                @foreach(Session::get('checkin-success')['alsoOnThisConnection'] as $otherStatus)
                        <li><a href="{{ route('account.show', ['username' => $otherStatus->user->username]) }}">{{ '@' . $otherStatus->user->username }}</a></li>
                @endforeach
                </ul>
            @endif
            @if(Session::get('checkin-success')['event'])
                <p>
                    {!!  __('events.on-your-way', [
                        "name" => Session::get('checkin-success')['event']['name'],
                        "url" => route('statuses.byEvent', ['eventSlug' => Session::get('checkin-success')['event']['slug']])
                    ]) !!}
                </p>
            @endif
            <hr>
            <p class="mb-0">
                <i class="fa fa-stopwatch d-inline"></i>&nbsp;<b>{!! durationToSpan(secondsToDuration(Session::get('checkin-success')['duration'] * 60)) !!}</b>
                — <i class="fa fa-route d-inline"></i>&nbsp;<b>{{ number(Session::get('checkin-success')['distance']) }}<small>km</small></b>
                — <i class="fa fa-dice-d20 d-inline"></i>&nbsp;<b>{{ Session::get('checkin-success')['points'] }}<small>{{__('profile.points-abbr')}}</small></b>
            </p>
        </div>
    @endif
    <div id="alert_placeholder"></div>
        </div>
    </div>
</div>
