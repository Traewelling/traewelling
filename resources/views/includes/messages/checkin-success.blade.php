@if(session()->has('checkin-success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <h4 class="alert-heading">
            <i class="fa-solid fa-check"></i>
            {{ __('controller.transport.checkin-heading') }}
        </h4>

        <p>
            <span>{{ trans_choice('controller.transport.checkin-ok', preg_match('/\s/', session()->get('checkin-success')['lineName']), ['lineName' => session()->get('checkin-success')['lineName']]) }}</span>
            @if(session()->get('checkin-success')["pointsCalculationReason"]  === App\Enum\PointReason::NOT_SUFFICIENT)
                <br/>
                <span style="display: block;" class="text-danger mt-2">
                    <i class="fa-solid fa-circle-info"></i>
                    {{__('checkin.points.could-have')}}
                    <a href="{{route('static.about') . '#heading-points'}}">
                        ({{__('messages.cookie-notice-learn')}})
                    </a>
                </span>
            @endif
            @if(session()->get('checkin-success')['points'] == 1 && session()->get('checkin-success')['forced'])
                <br/>
                <span class="text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    {{__('no-points-message')}}
                </span>
            @endif
        </p>
        @if(session()->get('checkin-success')['alsoOnThisConnection']->count() >= 1)
            <span>{{ trans_choice('controller.transport.also-in-connection', session()->get('checkin-success')['alsoOnThisConnection']->count()) }}</span>
            <table style="margin-left: auto;margin-right: auto;">
                <tbody>
                    @foreach(session()->get('checkin-success')['alsoOnThisConnection'] as $otherStatus)
                        @if(request()->user()->cannot('view', $otherStatus))
                            <tr>
                                <td colspan="5">
                                    <i class="fas fa-user-secret" aria-hidden="true"></i>
                                    {{__('user.private-profile')}}
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td>
                                    <i class="fas fa-user" aria-hidden="true"></i>
                                    <a href="{{ route('profile', ['username' => $otherStatus->user->username]) }}">
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
                {{ number(session()->get('checkin-success')['distance'] / 1000) }}
                <small>km</small>
            </b>
            —
            <i class="fa fa-dice-d20 d-inline"></i>&nbsp;
            <b>{{ session()->get('checkin-success')['points'] }}<small>{{__('profile.points-abbr')}}</small></b>
        </p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
