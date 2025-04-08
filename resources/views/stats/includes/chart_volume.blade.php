<div class="card">
    <div class="card-body">
        <h5>{{__('stats.time')}}</h5>
        @if($travelTime->count() > 0)
            <div id="chart_triptime_calendar"></div>
        @else
            <p class="text-danger font-weight-bold mt-2">{{__('stats.no-data')}}</p>
        @endif
    </div>
</div>

@section('footer')
    @parent
    @if($travelTime->count() > 0)
        <script>
            let chartTripTimeCalendarName = '{{__('stats.time')}}';
            let chartTripTimeCalendarData = [
                    @foreach($travelTime as $row)
                {
                    x: new Date('{{$row->date->toIso8601String()}}').getTime(),
                    y: {{$row->duration ?? 0}}
                },
                @endforeach
            ];
            let chartTripTimeCalendarMinutes = "{{__('time.minutes')}}";
            ;
        </script>
    @endif
@endsection
