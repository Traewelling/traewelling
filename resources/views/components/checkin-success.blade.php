@php use App\Enum\PointReason; @endphp
<div>
    <div class="alert alert-success fade show" role="alert">
        <button type="button" class="btn-close float-end" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">
            <i class="fa-solid fa-check"></i>
            @if($reason === 'status-updated')
                {{ __('status.update.success') }}
            @else
                {{ __('controller.transport.checkin-heading') }}
            @endif
        </h4>

        <p>
            <span>{{ trans_choice('controller.transport.checkin-ok', preg_match('/\s/', $lineName), ['lineName' => $lineName]) }}</span>
            @if($pointReason === PointReason::NOT_SUFFICIENT)
                <br/>
                <span style="display: block;" class="text-danger mt-2">
                    <i class="fa-solid fa-circle-info"></i>
                    {{__('checkin.points.could-have')}}
                    <a href="https://help.traewelling.de/faq/" target="_blank">
                        ({{__('messages.cookie-notice-learn')}})
                    </a>
                </span>
            @endif
            @if($points === 0 && $pointReason === PointReason::MANUAL_TRIP)
                <br/>
                <span class="text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    {{__('no-points-message.manual')}}
                </span>
            @endif
            @if($points === 1 && $forced)
                <br/>
                <span class="text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    {{__('no-points-message.forced')}}
                </span>
            @endif
        </p>
        @if($alsoOnThisConnection->count() >= 1)
            <span>{{ trans_choice('controller.transport.also-in-connection', $alsoOnThisConnection->count()) }}</span>
            <table style="margin-left: auto;margin-right: auto;">
                <tbody>
                    @foreach($alsoOnThisConnection as $otherStatus)
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
                                <td>{{ $otherStatus->checkin->originStopover->station->name }}</td>
                                <td>➜</td>
                                <td>{{ $otherStatus->checkin->destinationStopover->station->name }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endif
        @if($event)
            <p>
                {!!  __('events.on-your-way', [
                    "name" => $event['name'],
                    "url" => route('event', ['slug' => $event['slug']])
                ]) !!}
            </p>
        @endif
        <hr>
        <p class="mb-0">
            <button
                    class="btn btn-outline-success btn-sm float-end trwl-share"
                    data-trwl-share-url="{{ route('status', ['id' => $id]) }}"
                    data-trwl-share-text="{{ $socialText }}"
            >
                <span class="d-none d-sm-inline">{{__('menu.share')}} </span><i class="fas fa-share" aria-hidden="true"></i>
                <span class="sr-only">{{__('menu.share')}}</span>
            </button>
            <i class="fa fa-stopwatch d-inline"></i>&nbsp;
            <b>{!! durationToSpan(secondsToDuration($duration * 60)) !!}</b>
            —
            <i class="fa fa-route d-inline"></i>&nbsp;
            <b>
                {{ number($distance / 1000) }}
                <small>km</small>
            </b>
            —
            <i class="fa fa-dice-d20 d-inline"></i>&nbsp;
            <b>{{ $points }}<small>{{__('profile.points-abbr')}}</small></b>
        </p>
    </div>
</div>
