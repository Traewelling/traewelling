<canvas id="hafasPolylinesCanvas" height="300"></canvas>

@section('scripts')
    @parent
    <script>
        new Chart(document.getElementById('hafasPolylinesCanvas').getContext('2d'), {
            type: 'line',
            data: {
                labels: [
                    @foreach($hafasAndPolylinesByDate as $row)
                        '{{userTime($row->date, 'd.m.Y', false)}}',
                    @endforeach
                ],
                datasets: [
                    {
                        label: 'HafasTrips',
                        backgroundColor: 'rgb(106,106,105)',
                        borderColor: 'rgb(106,106,105)',
                        fill: false,
                        data: [
                            @foreach($hafasAndPolylinesByDate as $row)
                                    {{$row->hafasTripsCount}},
                            @endforeach
                        ]
                    }, {
                        label: 'Polylines',
                        backgroundColor: 'rgb(226,22,226)',
                        borderColor: 'rgb(226,22,226)',
                        data: [
                            @foreach($hafasAndPolylinesByDate as $row)
                                    {{$row->polyLineCount}},
                            @endforeach
                        ]
                    }
                ]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: '{{__('admin.hafas-entries-by-polylines')}}'
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
