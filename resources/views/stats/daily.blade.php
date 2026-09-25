@extends(appLayout())
@section('title', __('stats-day', ['date' => $date->isoFormat(__('dateformat.with-weekday'))]))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 mb-3">
                <h1 class="fs-4">{{__('stats-day', ['date' => $date->isoFormat(__('dateformat.with-weekday'))])}}</h1>

                @if($prevDate)
                    <a href="{{route('stats.daily', ['dateString' => $prevDate->format('Y-m-d')])}}"
                       class="btn btn-primary"
                    >
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        {{userTime($prevDate, __('date-format'))}}
                    </a>
                @endif
                @if($nextDate)
                    <a href="{{route('stats.daily', ['dateString' => $nextDate->format('Y-m-d')])}}"
                       class="btn btn-primary float-end"
                    >
                        {{userTime($nextDate, __('date-format'))}}
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                @endif
            </div>

            <div id="vue-stats-daily" class="col-12">
                <stats-daily :date="'{{ $date->format('Y-m-d') }}'"></stats-daily>
            </div>
        </div>
    </div>
@endsection
