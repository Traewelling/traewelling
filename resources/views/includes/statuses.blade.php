@foreach($statuses as $status)
    @if($showDates && ($loop->first || !$status->trainCheckin->departure->isSameDay($statuses[$loop->index - 1]->trainCheckin->departure)))
        <h2 class="mb-2 fs-5">{{$status->trainCheckin->departure->isoFormat(__('dateformat.with-weekday'))}}</h2>
    @endif
    @include('includes.status')
@endforeach
