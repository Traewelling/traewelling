<h4>Globale Statistiken</h4>
<hr/>
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-3 text-center">
                <i class="fas fa-ruler fa-5x"></i>
            </div>
            <div class="col-9 text-center">
                <span style="font-size: 40px;" class="font-weight-bold">
                    {{number($globalStats->distance, 0)}} km
                </span>
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
                <span style="font-size: 40px;" class="font-weight-bold">
                    {!! durationToSpan(secondsToDuration($globalStats->duration)) !!}
                </span>
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
                <span style="font-size: 40px;" class="font-weight-bold">
                    {{$globalStats->user_count}}x
                </span>
                <br/>
                <small class="text-muted">aktive Träwelling Nutzer in den letzten 24h</small>
            </div>
        </div>
    </div>
</div>
<hr/>