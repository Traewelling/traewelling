@foreach($statuses as $status)
    @if($showDates && ($loop->first || !$status->checkin->departure->isSameDay($statuses[$loop->index - 1]->checkin->departure)))
        <h2 class="mb-2 fs-5">
            {{userTime($status->checkin->departure, __('dateformat.with-weekday'))}}
            @if(Route::is('profile') && $user->is(Auth::user()))
                <a href="{{route('stats.daily', ['dateString' => $status->checkin->departure->toDateString()])}}"
                   class="text-trwl"
                >
                    <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
                </a>
            @endif
        </h2>
    @endif
    @include('includes.status')
@endforeach
