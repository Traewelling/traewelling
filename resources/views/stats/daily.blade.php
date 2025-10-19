@extends('layouts.app')
@section('title', __('stats-day', ['date' => $date->isoFormat(__('dateformat.with-weekday'))]))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 mb-3">
                <h1 class="fs-4">{{__('stats-day', ['date' => $date->isoFormat(__('dateformat.with-weekday'))])}}</h1>

                <a href="{{route('stats.daily', ['dateString' => userTime($date->clone()->subDay(), 'Y-m-d', false)])}}"
                   class="btn btn-primary"
                >
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    {{userTime($date->clone()->subDay(),__('date-format'))}}
                </a>
                @if($date->clone()->addDay()->isBefore(\Illuminate\Support\Facades\Date::today()->endOfDay()))
                    <a href="{{route('stats.daily', ['dateString' => $date->clone()->addDay()->format('Y-m-d')])}}"
                       class="btn btn-primary float-end"
                    >
                        {{userTime($date->clone()->addDay(), __('date-format'))}}
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

            <div id="vue-stats-daily" class="col-md-6">
                <stats-daily :date="'{{ $date->format('Y-m-d') }}'"></stats-daily>
            </div>
        </div>
    </div>
@endsection
