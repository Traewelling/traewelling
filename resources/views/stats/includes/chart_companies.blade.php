<div class="card">
    <div class="card-body">
        <h5>{{__('stats.companies')}}</h5>
        @if($topOperators->count() > 0)
            <canvas id="chart_companies"></canvas>
            <hr/>
            <table>
                @foreach($topOperators as $operator)
                    <tr>
                        <td>{{$operator->count}} {{__('stats.trips')}}</td>
                        <td>{{$operator->name}}</td>
                    </tr>
                @endforeach
            </table>
        @else
            <p class="text-danger font-weight-bold mt-2">{{__('stats.no-data')}}</p>
        @endif
    </div>
</div>

@section('javascript-end')
    @parent
    @if($topOperators->count() > 0)
        <script>
            new Chart(document.getElementById('chart_companies').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: [
                        @foreach($topOperators as $operator)
                            '{{$operator->name}}',
                        @endforeach
                    ],
                    datasets: [{
                        data: [
                            @foreach($topOperators as $operator)
                                '{{$operator->count}}',
                            @endforeach
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.4)',
                            'rgba(54, 162, 235, 0.4)',
                            'rgba(255, 206, 86, 0.4)',
                            'rgba(75, 192, 192, 0.4)',
                            'rgba(153, 102, 255, 0.4)',
                            'rgba(255, 159, 64, 0.4)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
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