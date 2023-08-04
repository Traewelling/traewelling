<canvas id="statusesByDateCanvas" height="300"></canvas>

@section('scripts')
    @parent
    <script>
        new Chart(document.getElementById('statusesByDateCanvas').getContext('2d'), {
            type: 'line',
            data: {
                labels: [
                    @foreach($statusesByDate as $row)
                    '{{userTime($row->date, 'd.m.Y', false)}}',
                    @endforeach
                ],
                datasets: [{
                    label: '{{__('admin.statuses')}}',
                    backgroundColor: 'rgb(255, 99, 132)',
                    borderColor: 'rgb(255, 99, 132)',
                    data: [
                        @foreach($statusesByDate as $row)
                            '{{$row->count}}',
                        @endforeach
                    ]
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: '{{__('admin.checkins')}}'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Date'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            suggestedMax: 10,
                            stepSize: 1
                        }
                    }]
                }
            }
        });
    </script>
@endsection
