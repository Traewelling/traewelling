<h4>Globale Statistiken</h4>
<hr/>
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-3 text-center">
                <i class="fas fa-ruler fa-5x"></i>
            </div>
            <div class="col-9 text-center">
                <span style="font-size: 2em;" class="font-weight-bold color-main">
                    {{number($globalStats->distance, 0)}} km
                </span>
                <br/>
                <small class="text-muted">Reisedistanz aller Träwelling Nutzer</small>
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
                <span style="font-size: 2em;" class="font-weight-bold color-main">
                    {!! durationToSpan(secondsToDuration($globalStats->duration)) !!}
                </span>
                <br/>
                <small class="text-muted">Reisezeit aller Träwelling Nutzer</small>
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
                <span style="font-size: 2em;" class="font-weight-bold color-main">
                    {{$globalStats->user_count}}x
                </span>
                <br/>
                <small class="text-muted">Aktive Träwelling Nutzer</small>
            </div>
        </div>
    </div>
</div>
<hr/>
<small class="text-muted">*Die Globalen Statistiken beziehen sich auf die CheckIns aller Träwelling Nutzer im Zeitraum
    von x bis x.</small>
<hr/>