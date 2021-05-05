<div class="card">
    <div class="card-body">
        <h5>Dein Reisevolumen <small>pro Kalenderwoche</small></h5>
        @if($travelTime->count() > 0)
            <canvas id="chart_triptime_calendar" style="width: 100%; height: 300px;"></canvas>
        @else
            <p class="text-danger font-weight-bold mt-2">Es sind keine Daten in dem Zeitraum vorhanden.</p>
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
                            'KW {{$row->date->isoFormat('w / Y')}}',
                        @endforeach
                    ],
                    datasets: [{
                        label: 'Reisezeit in Minuten',
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