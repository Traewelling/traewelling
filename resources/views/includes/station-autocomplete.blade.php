<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
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

                        <div class="input-group mb-2 mr-sm-2">
                            <input type="text" id="station-autocomplete" name="station" class="form-control" placeholder="{{ __('stationboard.station-placeholder') }}" @isset(request()->station) value="{{request()->station}}" @endisset>

                            @if($latest->count() > 0 || Auth::user()->home)
                            <div class="input-group-append" id="history-button" title="{{__('stationboard.last-stations')}}">
                                <span class="input-group-text" id="basic-addon2">
                                    <i class="fa fa-history"></i>
                                </span>
                            </div>
                            @endif
                            
                            <div class="input-group-append" id="gps-button" title="{{__('stationboard.search-by-location')}}">
                                <span class="input-group-text" id="basic-addon2">
                                    <i class="fa fa-map-marker-alt"></i>
                                </span>
                            </div>

                        </div>
                        <div class="list-group d-none" id="last-stations">
                            @if(Auth::user()->home)
                                <a href="{{ route('trains.stationboard', ['provider' => 'train', 'station' => Auth::user()->home->name ]) }}"
                                    title="{{ Auth::user()->home->name }}" id="home-button" class="list-group-item list-group-item-action">
                                    <i class="fa fa-home mr-2"></i> {{ Auth::user()->home->name }}
                                </a>
                            @endif

                            @if($latest->count())
                            <span class="list-group-item title list-group-item-action disabled">{{__('stationboard.last-stations')}}</span>
                            @endif
                            @foreach($latest as $station)
                                <a href="{{ route('trains.stationboard', ['provider' => 'train', 'station' => $station->name ]) }}"
                                    title="{{ $station->name }}" id="home-button" class="list-group-item list-group-item-action">
                                    {{ $station->name }}
                                </a>
                            @endforeach
                        </div>

                        <input type="hidden" name="when" value="{{@$request->when}}">
                        <button class="btn btn-outline-primary float-right" type="Submit">{{__('stationboard.submit-search')}}</button>
                        <button class="btn btn-outline-secondary" type="button" data-toggle="collapse" data-target="#collapseFilter" aria-expanded="false" aria-controls="collapseExample">{{__('stationboard.filter-products')}}</button>
                        <div class="collapse" id="collapseFilter">
                            <div class="mt-3 d-flex justify-content-center">
                                <div class="btn-group flex-wrap btn-group-sm" role="group">
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="ferry">{{ __('transport_types.ferry') }}</button>
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="bus">{{ __('transport_types.bus') }}</button>
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="tram">{{ __('transport_types.tram') }}</button>
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="subway">{{ __('transport_types.subway') }}</button>
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="suburban">{{ __('transport_types.suburban') }}</button>
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="regional">{{ __('transport_types.regional') }}</button>
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="express">{{ __('transport_types.express') }}</button>
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
