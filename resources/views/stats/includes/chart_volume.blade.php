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
            new ApexCharts(document.querySelector("#chart_triptime_calendar"), {
                series: [
                    {
                        name: '{{__('stats.time-in-minutes')}}',
                        data: [
                                @foreach($travelTime as $row)
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
                            return value + ' {{__('time.minutes')}}';
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
            }).render();
        </script>
    @endif
@endsection
