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
            new ApexCharts(document.querySelector("#chart_purpose"), {
                chart: {
                    type: 'pie'
                },
                series: [
                    @foreach($travelPurposes as $row)
                        {{$row->duration}},
                    @endforeach
                ],
                labels: [
                    @foreach($travelPurposes as $row)
                        '{{$row->reason}}',
                    @endforeach
                ],
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return value + ' {{__('time.minutes')}}';
                        }
                    }
                },
            }).render();
        </script>
    @endif
@endsection
