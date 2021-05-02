@extends('layouts.app')

@section('title')Statistiken @endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>Deine Lieblingsverkehrsunternehmen</h5>
                                <canvas id="myChart" style="width: 100%; height: 300px;"></canvas>
                                <hr/>
                                <table>
                                    <tr>
                                        <td>12%</td>
                                        <td>DB Fernverkehr AG</td>
                                    </tr>
                                    <tr>
                                        <td>19%</td>
                                        <td>metronom Eisenbahngesellschaft mbH</td>
                                    </tr>
                                    <tr>
                                        <td>3%</td>
                                        <td>üstra Hannoversche Verkehrsbetriebe AG</td>
                                    </tr>
                                    <tr>
                                        <td>5%</td>
                                        <td>Wiener Linien</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <script>
                            var ctx = document.getElementById('myChart').getContext('2d');
                            var myChart = new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: [
                                        'DB Fernverkehr AG',
                                        'metronom Eisenbahngesellschaft mbH',
                                        'üstra Hannoversche Verkehrsbetriebe AG',
                                        'Wiener Linien',
                                    ],
                                    datasets: [{
                                        label: '# of Votes',
                                        data: [12, 19, 3, 5],
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
                                    <tr>
                                        <td>12%</td>
                                        <td>ICE</td>
                                    </tr>
                                    <tr>
                                        <td>19%</td>
                                        <td>RE</td>
                                    </tr>
                                    <tr>
                                        <td>3%</td>
                                        <td>Tram</td>
                                    </tr>
                                    <tr>
                                        <td>5%</td>
                                        <td>S</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <script>
                            var ctx = document.getElementById('chart_favourite_types').getContext('2d');
                            var myChart = new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: [
                                        'DB Fernverkehr AG',
                                        'metronom Eisenbahngesellschaft mbH',
                                        'üstra Hannoversche Verkehrsbetriebe AG',
                                        'Wiener Linien',
                                    ],
                                    datasets: [{
                                        label: '# of Votes',
                                        data: [12, 19, 3, 5],
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
                <small class="text-muted">betrifft die Reisen aller Träwelling Nutzer der letzten 24 Stunden</small>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3 text-center">
                                <i class="fas fa-ruler fa-5x"></i>
                            </div>
                            <div class="col-9 text-center">
                                <span style="font-size: 40px;" class="font-weight-bold">1.423 km</span>
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
                                <span style="font-size: 40px;" class="font-weight-bold">827h 23min</span>
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
                                <span style="font-size: 40px;" class="font-weight-bold">34x</span>
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
                                <span style="font-size: 40px;" class="font-weight-bold">6x ICE, </span>
                                <span style="font-size: 30px;" class="font-weight-bold">3x RE, </span>
                                <span style="font-size: 20px;" class="font-weight-bold">2x Bus, </span>
                                <span style="font-size: 15px;" class="font-weight-bold">1x S</span>
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
                                <span style="font-size: 40px;" class="font-weight-bold">Hannover Hbf</span>
                                <br/>
                                <small class="text-muted">Bahnhof mit den meisten Zwischenhalten der letzten 24h</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
