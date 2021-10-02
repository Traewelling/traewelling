@if(session()->has('checkin-success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __('controller.transport.checkin-heading') }}</h4>
        <p>{{ trans_choice('controller.transport.checkin-ok', preg_match('/\s/', session()->get('checkin-success')['lineName']), ['lineName' => session()->get('checkin-success')['lineName']]) }}</p>
        @if(session()->get('checkin-success')['alsoOnThisConnection']->count() >= 1)
            <p>{{ trans_choice('controller.transport.also-in-connection', session()->get('checkin-success')['alsoOnThisConnection']->count()) }}</p>
            <table style="margin-left: auto;margin-right: auto;">
                <tbody>
                    @foreach(session()->get('checkin-success')['alsoOnThisConnection'] as $otherStatus)
                        @if($otherStatus->statusInvisibleToMe)
                            <tr>
                                <td colspan="5">👻 {{__('user.private-profile')}}</td>
                            </tr>
                        @else
                            <tr>
                                <td>
                                    👤
                                    <a href="{{ route('account.show', ['username' => $otherStatus->user->username]) }}">
                                        {{ '@' . $otherStatus->user->username }}
                                    </a>
                                </td>
                                <td>-</td>
                                <td>{{ $otherStatus->trainCheckin->Origin->name }}</td>
                                <td>➜</td>
                                <td>{{ $otherStatus->trainCheckin->Destination->name }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endif
        @if(session()->get('checkin-success')['event'])
            <p>
                {!!  __('events.on-your-way', [
                    "name" => session()->get('checkin-success')['event']['name'],
                    "url" => route('statuses.byEvent', ['eventSlug' => session()->get('checkin-success')['event']['slug']])
                ]) !!}
            </p>
        @endif
        <hr>
        <p class="mb-0">
            <i class="fa fa-stopwatch d-inline"></i>&nbsp;
            <b>{!! durationToSpan(secondsToDuration(session()->get('checkin-success')['duration'] * 60)) !!}</b>
            —
            <i class="fa fa-route d-inline"></i>&nbsp;
            <b>
                {{ number(session()->get('checkin-success')['distance']) }}
                <small>km</small>
            </b>
            —
            <i class="fa fa-dice-d20 d-inline"></i>&nbsp;
            <b>{{ session()->get('checkin-success')['points'] }}<small>{{__('profile.points-abbr')}}</small></b>
        </p>
    </div>
@endif
