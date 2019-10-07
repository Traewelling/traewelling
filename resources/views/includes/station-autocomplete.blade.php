<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Where are you?</div>

                <div class="card-body">
                    <form action="{{ route('trains.stationboard') }}" method="get" id="autocomplete-form">
                        <input type="hidden" id="autocomplete-provider" name="provider" value="train">
                        <input type="text" id="station-autocomplete" name="station" class="form-control mb-2 mr-sm-2" aria-label="Text input with dropdown button" placeholder="Station" @isset(request()->station) value="{{request()->station}}" @endisset>
                        <input type="hidden" name="when" value="{{$request->when}}">
                        <button class="btn btn-outline-primary float-right" type="Submit">submit</button>
                        <button class="btn btn-outline-secondary" type="button" data-toggle="collapse" data-target="#collapseFilter" aria-expanded="false" aria-controls="collapseExample">Filter</button>
                        <div class="collapse" id="collapseFilter">
                            <div class="mt-3 d-flex justify-content-center">
                                <div class="btn-group flex-wrap btn-group-sm" role="group" aria-label="Basic example">
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="ferry">{{ __('Ferry') }}</button>
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="bus">{{ __('Bus') }}</button>
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="tram">{{ __('Tram') }}</button>
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="subway">{{ __('Subway') }}</button>
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="suburban">{{ __('Suburban') }}</button>
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="regional">{{ __('Regional') }}</button>
                                    <button type="submit" class="btn btn-unique btn-sm" name="travelType" value="express">{{ __('Express') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
&nbsp;
