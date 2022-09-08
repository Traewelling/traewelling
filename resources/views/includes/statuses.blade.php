@foreach($statuses as $status)
    @if($showDates && ($loop->first || !$status->effective_at->isSameDay($statuses[$loop->index - 1]->effective_at)))
        <h2 class="mb-2 fs-5">{{$status->effective_at->isoFormat(__('dateformat.with-weekday'))}}</h2>
    @endif
    @if($status->type === 'hafas')
        @include('includes.status-hafas')
    @elseif($status->type === 'location')
        @include('includes.status-location')
    @endif
@endforeach
