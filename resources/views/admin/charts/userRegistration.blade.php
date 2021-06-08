<canvas id="userRegistrationCanvas" height="300"></canvas>

@section('scripts')
    @parent
    <script>
        new Chart(document.getElementById('userRegistrationCanvas').getContext('2d'), {
            type: 'line',
            data: {
                labels: [
                    @foreach($registrationsByDate as $row)
                        '{{$row->date->format('d.m.Y')}}',
                    @endforeach
                ],
                datasets: [{
                    label: '{{__('admin.new-users')}}',
                    backgroundColor: 'rgb(255,159,64)',
                    borderColor: 'rgb(255,159,64)',
                    data: [
                        @foreach($registrationsByDate as $row)
                            '{{$row->count}}',
                        @endforeach
                    ]
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: '{{__('admin.registered-users')}}'
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
