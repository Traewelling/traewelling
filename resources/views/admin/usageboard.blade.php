@extends('layouts.admin')

@section('title')
    {{__('admin.usage')}}
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="card-title">{{__('admin.usage-board')}}</div>
        </div>
        <div class="card-body">
            <form method="get" class="row">
                @csrf
                <div class="col-6 col-md-4">
                    <label for="begin">{{__('export.begin')}}:</label>
                    <input name="begin" type="date" value="{{ $begin }}" class="form-control">
                </div>
                <div class="col-6 col-md-4">
                    <label for="end">{{__('export.end')}}:</label>
                    <input name="end" type="date" value="{{ $end }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="btn">&nbsp;</label>
                    <input type="submit" id="btn btn-primary" value="{{__('admin.select-range')}}"
                           class="btn-primary form-control">
                </div>
            </form>

            <div class="row pt-4">
                <div class="col-md-3">
                    <canvas id="statusesByDateCanvas" height="300"></canvas>
                </div>
                <div class="col-md-3">
                    <canvas id="userRegistrationCanvas" height="300"></canvas>
                </div>
                <div class="col-md-3">
                    <canvas id="transportTypesCanvas" height="300"></canvas>
                </div>
                <div class="col-md-3">
                    <h5 class="text-center pt-3"></h5>
                    <canvas id="hafasPolylinesCanvas" height="300"></canvas>
                </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        const dates = {!! json_encode($dates) !!};
        const usersJson = {!! json_encode($userRegistrationsByDay) !!};
        const statusJson = {!! json_encode($statusesByDay) !!};
        const transportJson = {!! json_encode($hafasTripsByDay) !!};

        let tramCount = transportJson.map(function(e) {
            return (e.tram === undefined) ? 0 : e.tram;
        });
        let busCount = transportJson.map(function(e) {
            return (e.bus === undefined) ? 0 : e.bus;
        });
        let subwayCount = transportJson.map(function(e) {
            return (e.subway === undefined) ? 0 : e.subway;
        });
        let suburbanCount = transportJson.map(function(e) {
            return (e.suburban === undefined) ? 0 : e.suburban;
        });
        let ferryCount = transportJson.map(function(e) {
            return (e.ferry === undefined) ? 0 : e.ferry;
        });
        let regionalCount = transportJson.map(function(e) {
            return (e.regional === undefined) ? 0 : e.regional;
        });
        let regionalExpCount = transportJson.map(function(e) {
            return (e.regionalExp === undefined) ? 0 : e.regionalExp;
        });
        let expressCount = transportJson.map(function(e) {
            return (e.express === undefined) ? 0 : e.express;
        });
        let nationalCount = transportJson.map(function(e) {
            return (e.national === undefined) ? 0 : e.national;
        });
        let nationalExpressCount = transportJson.map(function(e) {
            return (e.nationalExpress === undefined) ? 0 : e.nationalExpress;
        });
        let hafasCount = transportJson.map(function(e) {
            return (e.hafas === undefined) ? 0 : e.hafas;
        });
        let polylineCount = transportJson.map(function(e) {
            return (e.polylines === undefined) ? 0 : e.polylines;
        });


        let statuses = document.getElementById('statusesByDateCanvas').getContext('2d');
        let users = document.getElementById('userRegistrationCanvas').getContext('2d');
        let transportTypes = document.getElementById('transportTypesCanvas').getContext('2d');
        let hafas = document.getElementById('hafasPolylinesCanvas').getContext('2d');

        new Chart(statuses, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: '{{__('admin.statuses')}}',
                    backgroundColor: 'rgb(255, 99, 132)',
                    borderColor: 'rgb(255, 99, 132)',
                    data: statusJson
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: '{{__('admin.checkins')}}'
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
        new Chart(users, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: '{{__('admin.new-users')}}',
                    backgroundColor: 'rgb(255,159,64)',
                    borderColor: 'rgb(255,159,64)',
                    data: usersJson
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
        new Chart(transportTypes, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: '{{__('transport_types.tram')}}',
                    borderColor: 'rgb(204,0,0)',
                    backgroundColor: 'rgb(204,0,0)',
                    fill: false,
                    borderDash: [5, 5],
                    data: tramCount
                },{
                    label: '{{__('transport_types.bus')}}',
                    borderColor: 'rgb(163,0,124)',
                    backgroundColor: 'rgba(0,0,0,0)',
                    fill: false,
                    data: busCount
                },{
                    label: '{{__('transport_types.subway')}}',
                    borderColor: 'rgb(0,97,167)',
                    backgroundColor: 'rgba(0,0,0,0)',
                    fill: false,
                    data: subwayCount
                },{
                    label: '{{__('transport_types.suburban')}}',
                    borderColor: 'rgb(0,114,53)',
                    backgroundColor: 'rgba(0,0,0,0)',
                    fill: false,
                    data: suburbanCount
                },{
                    label: '{{__('transport_types.ferry')}}',
                    borderColor: 'rgb(0,128,192)',
                    backgroundColor: 'rgba(0,0,0,0)',
                    fill: false,
                    borderDash: [5, 5],
                    data: ferryCount
                },{
                    label: '{{__('transport_types.regional')}}',
                    borderColor: 'rgb(116,116,116)',
                    backgroundColor: 'rgba(0,0,0,0)',
                    fill: false,
                    data: regionalCount
                },{
                    label: '{{__('transport_types.regional')}}',
                    borderColor: 'rgb(116,116,116)',
                    backgroundColor: 'rgb(116,116,116)',
                    fill: false,
                    borderDash: [5, 5],
                    data: regionalExpCount
                },{
                    label: '{{__('transport_types.express')}}',
                    borderColor: 'rgb(68,126,188)',
                    backgroundColor: 'rgba(0,0,0,0)',
                    fill: false,
                    data: expressCount
                },{
                    label: '{{__('transport_types.national')}}',
                    borderColor: 'rgb(146,146,146)',
                    backgroundColor: 'rgba(0,0,0,0)',
                    fill: false,
                    data: nationalCount
                },{
                    label: '{{__('transport_types.nationalExpress')}}',
                    borderColor: 'rgb(255,3,3)',
                    backgroundColor: 'rgba(0,0,0,0)',
                    fill: false,
                    data: nationalExpressCount
                },]
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
        new Chart(hafas, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'HafasTrips',
                    backgroundColor: 'rgb(106,106,105)',
                    borderColor: 'rgb(106,106,105)',
                    fill: false,
                    data: hafasCount
                },{
                    label: 'Polylines',
                    backgroundColor: 'rgb(226,22,226)',
                    borderColor: 'rgb(226,22,226)',
                    data: polylineCount
                }]
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

