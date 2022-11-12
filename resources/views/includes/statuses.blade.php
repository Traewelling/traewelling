@foreach($statuses as $status)
    @if($showDates && ($loop->first || !$status->trainCheckin->departure->isSameDay($statuses[$loop->index - 1]->trainCheckin->departure)))
        <h2 class="mb-2 fs-5">
            {{$status->trainCheckin->departure->isoFormat(__('dateformat.with-weekday'))}}
            @if(Route::is('profile') && $user->is(Auth::user()))
                <a href="{{route('stats.daily', ['dateString' => $status->trainCheckin->departure->toDateString()])}}"
                   class="text-trwl"
                >
                    <i class="fa-solid fa-map-location-dot float-end" aria-hidden="true"></i>
                </a>
            @endif
        </h2>
    @endif
    @include('includes.status')
@endforeach
