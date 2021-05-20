<div class="card">
    <div class="card-body">
        <h5>{{__('stats.volume')}} <small>{{__('stats.per-week')}}</small></h5>
        @if($travelTime->count() > 0)
            <canvas id="chart_triptime_calendar"></canvas>
        @else
            <p class="text-danger font-weight-bold mt-2">{{__('stats.no-data')}}</p>
        @endif
    </div>
</div>

@section('javascript-end')
    @parent
    @if($travelTime->count() > 0)
        <script>
            new Chart(document.getElementById('chart_triptime_calendar').getContext('2d'), {
                type: 'line',
                data: {
                    labels: [
                        @foreach($travelTime as $row)
                            '{{__('stats.week-short')}} {{$row->date->isoFormat('w / Y')}}',
                        @endforeach
                    ],
                    datasets: [{
                        label: '{{__('stats.time-in-minutes')}}',
                        data: [
                            @foreach($travelTime as $row)
                                    {{$row->duration}},
                            @endforeach
                        ],
                        backgroundColor: [
                            '#c72730'
                        ],
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            });
        </script>
    @endif
@endsection