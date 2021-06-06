<canvas id="transportTypesCanvas" height="300"></canvas>

@section('scripts')
    @parent
    <script>
        let colors = [
            ['rgb(204,0,0)', 'rgb(204,0,0)'],
            ['rgb(163,0,124)', 'rgba(0,0,0,0)'],
            ['rgb(0,97,167)', 'rgba(0,0,0,0)'],
            ['rgb(0,114,53)', 'rgba(0,0,0,0)'],
            ['rgb(0,128,192)', 'rgba(0,0,0,0)'],
            ['rgb(116,116,116)', 'rgba(0,0,0,0)'],
            ['rgb(116,116,116)', 'rgb(116,116,116)'],
            ['rgb(68,126,188)', 'rgba(0,0,0,0)'],
            ['rgb(146,146,146)', 'rgba(0,0,0,0)'],
            ['rgb(255,3,3)', 'rgba(0,0,0,0)'],
        ];

        new Chart(document.getElementById('transportTypesCanvas').getContext('2d'), {
            type: 'line',
            data: {
                labels: [
                    @foreach($transportTypesByDate->first() as $rows)
                        '{{$rows->date->format('d.m.Y')}}',
                    @endforeach
                ],
                datasets: [
                        @foreach($transportTypesByDate as $category => $rows)
                    {
                        label: '{{__('transport_types.' . $category)}} ({{$category}})',
                        borderColor: colors[{{$loop->index}}][0],
                        backgroundColor: colors[{{$loop->index}}][0],
                        fill: false,
                        borderDash: [5, 5],
                        data: [
                            @foreach($rows as $row)
                                    {{$row->count ?? 0}},
                            @endforeach
                        ]
                    },
                    @endforeach
                ]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: '{{__('admin.hafas-entries-by-type')}}'
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