@foreach($statuses as $status)
    @if($showDates && ($loop->first || !$status->trainCheckin->departure->isSameDay($statuses[$loop->index - 1]->trainCheckin->departure)))
        <h5 class="mt-4">{{$status->trainCheckin->departure->isoFormat('dddd, DD. MMMM YYYY')}}</h5>
    @endif
    @include('includes.status')
@endforeach