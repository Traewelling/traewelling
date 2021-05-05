@extends('layouts.app')

@section('title')Statistiken @endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">

                <h4>Persönliche Statistiken vom {{$from->format('d.m.Y')}} bis {{$to->format('d.m.Y')}}</h4>
                <hr />
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>Deine Lieblingsverkehrsunternehmen</h5>
                                <canvas id="myChart" style="width: 100%; height: 300px;"></canvas>
                                <hr/>
                                <table>
                                    @foreach($topOperators as $operator)
                                        <tr>
                                            <td>{{$operator->count}} Fahrten</td>
                                            <td>{{$operator->name}}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        <script>
                            var ctx = document.getElementById('myChart').getContext('2d');
                            var myChart = new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: [
                                        @foreach($topOperators as $operator)
                                            '{{$operator->name}}',
                                        @endforeach
                                    ],
                                    datasets: [{
                                        label: '# of Votes',
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
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>Deine Lieblingsreisetypen</h5>
                                <canvas id="chart_favourite_types" style="width: 100%; height: 300px;"></canvas>
                                <hr/>
                                <table>
                                    @foreach($topCategories as $category)
                                        <tr>
                                            <td>{{$category->count}} Fahrten</td>
                                            <td>{{$category->category}}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        <script>
                            var ctx = document.getElementById('chart_favourite_types').getContext('2d');
                            var myChart = new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: [
                                        @foreach($topCategories as $category)
                                            '{{$category->category}}',
                                        @endforeach
                                    ],
                                    datasets: [{
                                        label: '# of Votes',
                                        data: [
                                            @foreach($topCategories as $category)
                                                '{{$category->count}}',
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
                    </div>

                    <hr />
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>Dein Reisevolumen</h5>
                                <canvas id="chart_triptime_calendar" style="width: 100%; height: 300px;"></canvas>
                            </div>
                        </div>
                        <script>
                            var ctx = document.getElementById('chart_triptime_calendar').getContext('2d');
                            var myChart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: [
                                        @for($date = \Carbon\Carbon::today()->subDays(100); $date->isBefore(\Carbon\Carbon::today()); $date->addDay())
                                            '{{$date->format('d.m.Y')}}',
                                        @endfor
                                    ],
                                    datasets: [{
                                        label: '# of Votes',
                                        data: [
                                            @for($date = \Carbon\Carbon::today()->subDays(100); $date->isBefore(\Carbon\Carbon::today()); $date->addDay())
                                                    {{rand(1,360)}},
                                            @endfor
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
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <h4>Globale Statistiken</h4>
                <hr />
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3 text-center">
                                <i class="fas fa-ruler fa-5x"></i>
                            </div>
                            <div class="col-9 text-center">
                                <span style="font-size: 40px;" class="font-weight-bold">{{number($globalStats->distance, 0)}} km</span>
                                <br/>
                                <small class="text-muted">(Reisen aller Träwelling Nutzer in den letzten 24h)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3 text-center">
                                <i class="fas fa-clock fa-5x"></i>
                            </div>
                            <div class="col-9 text-center">
                                <span style="font-size: 40px;" class="font-weight-bold">
                                    {!! durationToSpan(secondsToDuration($globalStats->duration)) !!}
                                </span>
                                <br/>
                                <small class="text-muted">(Reisen aller Träwelling Nutzer in den letzten 24h)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3 text-center">
                                <i class="fas fa-users fa-5x"></i>
                            </div>
                            <div class="col-9 text-center">
                                <span style="font-size: 40px;"
                                      class="font-weight-bold">{{$globalStats->user_count}}x</span>
                                <br/>
                                <small class="text-muted">aktive Träwelling Nutzer in den letzten 24h</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3 text-center">
                                <i class="fas fa-train fa-5x"></i>
                            </div>
                            <div class="col-9 text-center">
                                <span style="font-size: 40px;" class="font-weight-bold">DEV, </span>
                                <span style="font-size: 30px;" class="font-weight-bold">DEV </span>
                                <br/>
                                <small class="text-muted">Meistgenutze Reisearten der letzten 24h</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3 text-center">
                                <i class="fas fa-home fa-5x"></i>
                            </div>
                            <div class="col-9 text-center">
                                <span style="font-size: 40px;" class="font-weight-bold">DEV</span>
                                <br/>
                                <small class="text-muted">Bahnhof mit den meisten Zwischenhalten der letzten 24h</small>
                            </div>
                        </div>
                    </div>
                </div>
                <hr />
            </div>
        </div>
    </div>
@endsection
