<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Where to go?</div>

                <div class="card-body">
                    <form action="{{ route('trains.stationboard') }}" method="get">
                        <input type="hidden" id="autocomplete-provider" name="provider" value="train">
                        <input type="text" id="station-autocomplete" name="station" class="form-control mb-2 mr-sm-2" aria-label="Text input with dropdown button" placeholder="Station" @isset(request()->station) value="{{request()->station}}" @endisset>

                        <button class="btn btn-outline-secondary" type="Submit">submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
&nbsp;
