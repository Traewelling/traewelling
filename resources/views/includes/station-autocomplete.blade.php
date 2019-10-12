<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{__('stationboard.where-are-you')}}</div>

                <div class="card-body">
                    <form action="{{ route('trains.stationboard') }}" method="get" id="autocomplete-form">
                        <input type="hidden" id="autocomplete-provider" name="provider" value="train">
                        <input type="text" id="station-autocomplete" name="station" class="form-control mb-2 mr-sm-2" placeholder="{{ __('stationboard.station-placeholder') }}" @isset(request()->station) value="{{request()->station}}" @endisset>
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
// Hier kommen jetzt die 25 größten Städte Deutschlands rein, damit die Maschine
// schon mal was zum Zeigen hat, auch wenn noch kein AJAX-Request passiert ist.
// Liste: https://de.wikipedia.org/wiki/Liste_der_Gro%C3%9Fst%C3%A4dte_in_Deutschland#Tabelle
const popularStations = [
    'Hamburg Hbf', 'Berlin Hbf', 'München Hbf', 'Köln Hbf', 'Frankfurt(Main)Hbf', 'Stuttgart Hbf',
    'Düsseldorf Hbf', 'Leipzig Hbf', 'Dortmund Hbf', 'Essen Hbf', 'Bremen Hbf', 'Dresden Hbf',
    'Hannover Hbf', 'Nürnberg Hbf', 'Duisburg Hbf', 'Bochum Hbf', 'Wuppertal Hbf', 'Bielefeld Hbf',
    'Bonn Hbf', 'Münster Hbf', 'Karlsruhe Hbf', 'Mannheim Hbf', 'Augsburg Hbf', 'Wiesbaden Hbf',
    'Mönchengladbach Hbf'
];

const input = document.getElementById('station-autocomplete');
var awesomplete = new Awesomplete(input, {
    minChars: 2,
    autoFirst: true,
    list: popularStations,
});
input.addEventListener('keyup', (event) => {
    if (input.value.length < 5) return;

    // Hier können wir dann auch irgendwann die Flixbus-API einbauen,
    // finds ohne getrackte Flixbusse eher sinnlos.

    // Hier ist nur Bahn-Stuff
    fetch("{{ url('transport/train/autocomplete') }}/" + encodeURI(input.value))
    .then(res => res.json())
    .then(json => {
        awesomplete.list = json.map(d => { return {
            value: d.name,
            label: d.name + " | Deutsche Bahn",
        }; });
    })
    .catch(error => console.error(error));
});
</script>
