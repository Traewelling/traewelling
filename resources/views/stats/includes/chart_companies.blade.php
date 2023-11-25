<div class="card">
    <div class="card-body">
        <h5>{{__('stats.companies')}}</h5>
        @if($topOperators->count() > 0)
            <div id="chart_companies"></div>
        @else
            <p class="text-danger font-weight-bold mt-2">{{__('stats.no-data')}}</p>
        @endif
    </div>
</div>

@section('footer')
    @parent
    @if($topOperators->count() > 0)
        <script>
            new ApexCharts(document.querySelector("#chart_companies"), {
                chart: {
                    type: 'pie'
                },
                series: [
                    @foreach($topOperators as $operator)
                        {{$operator->duration}},
                    @endforeach
                ],
                labels: [
                    @foreach($topOperators as $operator)
                        '{{$operator->name ?? __('other')}}',
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
