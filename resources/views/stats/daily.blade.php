@extends('layouts.app')

@section('title', __('stats-day', ['date' => $date->isoFormat(__('dateformat.with-weekday'))]))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 mb-3">
                <h1 class="fs-4">{{__('stats-day', ['date' => $date->isoFormat(__('dateformat.with-weekday'))])}}</h1>

                <a href="{{route('stats.daily', ['dateString' => $date->clone()->subDay()->format('Y-m-d')])}}"
                   class="btn btn-primary"
                >
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    {{$date->clone()->subDay()->isoFormat(__('date-format'))}}
                </a>
                @if($date->clone()->addDay()->isBefore(\Illuminate\Support\Facades\Date::today()->endOfDay()))
                    <a href="{{route('stats.daily', ['dateString' => $date->clone()->addDay()->format('Y-m-d')])}}"
                       class="btn btn-primary float-end"
                    >
                        {{$date->clone()->addDay()->isoFormat(__('date-format'))}}
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                @endif
            </div>

            <div class="col-md-6 mb-4">
                <div id="map" style="min-height: 600px;"></div>
                <script>
                    window.addEventListener("load", () => {
                        let map = L.map(document.getElementById('map'), {
                            center: [50.3, 10.47],
                            zoom: 5
                        });

                        let featureGroup = L.featureGroup().addTo(map);

                        setTilingLayer(mapprovider, map);

                        @foreach($statuses as $status)
                        try {
                        let coordinates = {{json_encode($status->mapLines)}};
                            L.polyline(coordinates)
                                .setStyle({color: "rgb(192, 57, 43)", weight: 5})
                                .addTo(featureGroup);
                        } catch (e) {
                            console.error(e);
                        }

                    @endforeach

                    map.fitBounds(featureGroup.getBounds());
                });

                </script>
            </div>
            <div class="col-md-6">
                @if($statuses->count() === 0)
                    <div class="alert alert-warning text-center fs-4">
                        {{__('no-journeys-day')}}
                    </div>
                @else
                    <div class="row text-center fs-5" id="daily-stats-statsbar">
                        <div class="col-6 mb-3 col-lg-3">
                            <i class="fa-solid fa-train"></i>
                            {{$statuses->count()}}
                            {{__('stats.trips')}}
                        </div>
                        <div class="col-6 mb-3 col-lg-3">
                            <i class="fa-solid fa-route"></i>
                            {{round($statuses->sum('trainCheckin.distance') / 1000)}} km
                        </div>
                        <div class="col-6 mb-3 col-lg-3">
                            <i class="fa-regular fa-clock"></i>
                            {!! durationToSpan(secondsToDuration($statuses->sum('trainCheckin.duration') * 60)) !!}
                        </div>
                        <div class="col-6 mb-3 col-lg-3">
                            <i class="fa fa-dice-d20"></i>
                            {{$statuses->sum('trainCheckin.points')}} {{__('profile.points-abbr')}}
                        </div>
                    </div>

                    @foreach($statuses as $status)
                        @include('includes.status')
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    @include('includes.edit-modal')
    @include('includes.delete-modal')
@endsection
