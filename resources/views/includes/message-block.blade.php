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

    @if(Session::has('message'))
        <div class="alert my-3 alert-info" role="alert">
            {!! Session::get('message') !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(Session::has('checkin-success'))
        @php
            $message = Session::get('checkin-success');
        @endphp
        <div class="alert alert-success" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="alert-heading">{{ __('controller.transport.checkin-heading') }}</h4>
            <p>{{ trans_choice('controller.transport.checkin-ok', preg_match('/\s/', $message['lineName']), ['lineName' => $message['lineName']]) }}</p>
            @if($message['alsoOnThisConnection']->count() >= 1)
                <p>{{ __('controller.transport.also-in-connection') }}</p>
                <ul>
                @foreach($message['alsoOnThisConnection'] as $person)
                        <li><a href="{{ route('account.show', ['username' => $person->username]) }}">{{ '@' . $person->username }}</a></li>
                @endforeach
                </ul>
            @endif
            <hr>
            <p class="mb-0">
                <i class="fa fa-stopwatch d-inline"></i>&nbsp;{!! durationToSpan(secondsToDuration($message['duration'])) !!}</b>
                — <i class="fa fa-route d-inline"></i>&nbsp;<b>{{ number($message['distance']) }}<small>km</small></b>
                — <i class="fa fa-dice-d20 d-inline"></i>&nbsp;<b>{{ $message['points'] }}<small>{{__('profile.points-abbr')}}</small></b>
            </p>
        </div>
    @endif
    <div id="alert_placeholder"></div>
        </div>
    </div>
</div>
