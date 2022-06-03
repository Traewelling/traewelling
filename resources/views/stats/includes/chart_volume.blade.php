<div class="card">
    <div class="card-body">
        @if($travelTime->count() > 0)
            <div id="chartTripTimeCalendar"></div>
        @else
            <h5>{{__('stats.time')}}</h5>
            <p class="text-danger font-weight-bold mt-2">{{__('stats.no-data')}}</p>
        @endif
    </div>
</div>

@section('footer')
    @parent
    @if($travelTime->count() > 0)
        <script>
            new ApexCharts(document.querySelector("#chartTripTimeCalendar"), {
                series: [
                        @foreach($travelTime as $month => $data)
                    {
                        name: '{{$month}}',
                        data: [
                                @foreach($data as $row)
                            {
                                x: '{{$row->date->day}}',
                                y: {{$row->duration ?? 0}}
                            },
                            @endforeach
                        ]
                    },
                    @endforeach
                ],
                chart: {
                    type: 'heatmap',
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    type: 'category',
                    categories: [
                        @for($i = 1; $i <= 31; $i++)
                            '{{$i}}',
                        @endfor
                    ]
                },
                colors: ["#C72730"],
                title: {
                    text: '{{__('stats.time')}}'
                },
                heatmap: {
                    colorScale: {
                        ranges: [{
                            from: 0,
                            to: 200,
                            color: '#C72730',
                            name: 'low',
                        }]
                    }
                }
            }).render();

            let old = {
                series: [
                    {
                        name: '{{__('stats.time-in-minutes')}}',
                        data: [
                                @foreach([] as $row)
                            {
                                x: new Date('{{$row->date->toIso8601String()}}').getTime(),
                                y: {{$row->duration ?? 0}}
                            },
                            @endforeach
                        ]
                    }
                ],
                chart: {
                    type: 'area',
                    stacked: false,
                    height: 350,
                    zoom: {
                        type: 'x',
                        enabled: true,
                        autoScaleYaxis: true
                    },
                    toolbar: {
                        autoSelected: 'zoom'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                markers: {
                    size: 0,
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        inverseColors: false,
                        opacityFrom: 0.5,
                        opacityTo: 0,
                        stops: [0, 90, 100]
                    },
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return value + ' {{__('minutes')}}';
                        }
                    },
                },
                xaxis: {
                    type: 'datetime',
                    labels: {
                        datetimeUTC: false,
                        datetimeFormatter: {
                            year: 'yyyy',
                            month: 'MMM \'yy',
                            day: 'dd MMM',
                            hour: 'HH:mm'
                        }
                    }
                },
                tooltip: {
                    shared: false,
                    x: {
                        format: 'dd MMM yyyy HH:mm'
                    }
                }
            };
        </script>
    @endif
@endsection
