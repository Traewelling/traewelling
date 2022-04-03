@extends('admin.layout')

@section('title', 'API Usage')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-2">
                <div class="card-body">
                    <form class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="datetime-local" class="form-control" id="inputStart" name="start"
                                       value="{{$start->toDateTimeLocalString()}}" placeholder="Von"/>
                                <label for="inputStart" class="form-label">
                                    Von
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="datetime-local" class="form-control" id="inputEnd" name="end"
                                       value="{{$end->toDateTimeLocalString()}}" placeholder="Bis"/>
                                <label for="inputEnd" class="form-label">
                                    Bis
                                </label>
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">Suchen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="fs-5" id="titleMostUsedRoutes">Meistgenutzte Routen</h2>

                    @if($mostUsedRoutes->count() === 0)
                        <p class="text-danger fw-bold">Keine Daten vorhanden</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" aria-labelledby="titleMostUsedRoutes">
                                <thead>
                                    <tr>
                                        <th>HTTP-Methode</th>
                                        <th>Route</th>
                                        <th>Anzahl</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mostUsedRoutes as $route)
                                        <tr>
                                            <td><code>{{ $route->method }}</code></td>
                                            <td><code>{{ $route->route }}</code></td>
                                            <td>{{ $route->count }}x</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="fs-5" id="titleUserAgents">UserAgents</h2>

                    @if($mostUsedUserAgents->count() === 0)
                        <p class="text-danger fw-bold">Keine Daten vorhanden</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" aria-labelledby="titleUserAgents">
                                <thead>
                                    <tr>
                                        <th>UserAgent</th>
                                        <th>Anzahl</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mostUsedUserAgents as $userAgentObject)
                                        <tr>
                                            <td><code>{{ $userAgentObject->user_agent }}</code></td>
                                            <td>{{ $userAgentObject->count }}x</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
