<div class="card">
    <div class="card-body">
        <h5>{{__('stats.purpose')}}</h5>
        @if($travelPurposes->count() > 0)
            <div id="chart_purpose"></div>
        @else
            <p class="text-danger font-weight-bold mt-2">{{__('stats.no-data')}}</p>
        @endif
    </div>
</div>

@section('footer')
    @parent
    @if($travelPurposes->count() > 0)
        <script>
            let chartPurposeSeries = [
                @foreach($travelPurposes as $row)
                    {{$row->duration}},
                @endforeach
            ];
            let chartPurposeLabels = [
                @foreach($travelPurposes as $row)
                    '{{$row->reason}}',
                @endforeach
            ];
            let chartPurposeMinutes = "{{ __('time.minutes') }}";
        </script>
    @endif
@endsection
