<div class="card">
    <div class="card-body">
        <h5>{{__('stats.categories')}}</h5>

        @if($topCategories->count() > 0)
            <div id="chart_favourite_types" style="height: 200px;"></div>
        @else
            <p class="text-danger font-weight-bold mt-2">{{__('stats.no-data')}}</p>
        @endif
    </div>
</div>

@section('footer')
    @parent
    @if($topCategories->count() > 0)
        <script>
            new ApexCharts(document.querySelector("#chart_favourite_types"), {
                chart: {
                    type: 'pie'
                },
                series: [
                    @foreach($topCategories as $category)
                        {{$category->duration}},
                    @endforeach
                ],
                labels: [
                    @foreach($topCategories as $category)
                        '{{$category->name}}',
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
