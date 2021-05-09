<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card">
                <div class="card-header">{{__('stationboard.where-are-you')}}</div>
                <div class="card-body">
                    <div id="gps-disabled-error" class="alert my-3 alert-danger d-none" role="alert">
                        {{__('stationboard.position-unavailable')}}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('trains.stationboard') }}" method="get" id="autocomplete-form">
                        <input type="hidden" id="autocomplete-provider" name="provider" value="train">
                        <div id="station-autocomplete-container">
                            <div class="input-group mb-2 mr-sm-2">
                                <input type="text" id="station-autocomplete" name="station" class="form-control"
                                       placeholder="{{ __('stationboard.station-placeholder') }} / DS100"
                                       @isset(request()->station) value="{{request()->station}}" @endisset
                                />

                                @if($latest->count() > 0 || Auth::user()->home)
                                    <div class="btn btn-outline-grey stationSearchButton"
                                         data-mdb-toggle="collapse"
                                         data-mdb-target="#last-stations"
                                         title="{{__('stationboard.last-stations')}}"
                                    >
                                        <i class="fa fa-history"></i>
                                    </div>
                                @endif

                                <div class="btn btn-outline-grey stationSearchButton" id="gps-button"
                                     title="{{__('stationboard.search-by-location')}}">
                                    <i class="fa fa-map-marker-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="list-group collapse" id="last-stations">
                            @if(Auth::user()->home)
                                <a href="{{ route('trains.stationboard', ['provider' => 'train', 'station' => Auth::user()->home->name ]) }}"
                                   title="{{ Auth::user()->home->name }}" id="home-button"
                                   class="list-group-item list-group-item-action">
                                    <i class="fa fa-home mr-2"></i> {{ Auth::user()->home->name }}
                                </a>
                            @endif

                            @if($latest->count())
                                <span class="list-group-item title list-group-item-action disabled">{{__('stationboard.last-stations')}}</span>
                            @endif
                            @foreach($latest as $station)
                                <a href="{{ route('trains.stationboard', ['provider' => 'train', 'station' => $station->name ]) }}"
                                   title="{{ $station->name }}" id="home-button"
                                   class="list-group-item list-group-item-action">
                                    {{ $station->name }}
                                </a>
                            @endforeach
                        </div>

                        <input type="hidden" name="when" value="{{@$request->when}}">
                        <button class="btn btn-outline-primary float-end" type="submit">
                            {{__('stationboard.submit-search')}}
                        </button>
                        <button class="btn btn-outline-secondary" type="button" data-mdb-toggle="collapse"
                                data-mdb-target="#collapseFilter" aria-expanded="false">
                            {{__('stationboard.filter-products')}}
                        </button>
                        <div class="collapse" id="collapseFilter">
                            <div class="mt-3 d-flex justify-content-center">
                                <div class="btn-group flex-wrap btn-group-sm" role="group">
                                    <button type="submit" class="btn btn-primary btn-sm" name="travelType"
                                            value="ferry">
                                        {{ __('transport_types.ferry') }}
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-sm" name="travelType" value="bus">
                                        {{ __('transport_types.bus') }}
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-sm" name="travelType" value="tram">
                                        {{ __('transport_types.tram') }}
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-sm" name="travelType"
                                            value="subway">
                                        {{ __('transport_types.subway') }}
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-sm" name="travelType"
                                            value="suburban">
                                        {{ __('transport_types.suburban') }}
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-sm" name="travelType"
                                            value="regional">
                                        {{ __('transport_types.regional') }}
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-sm" name="travelType"
                                            value="express">
                                        {{ __('transport_types.express') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
</script>
