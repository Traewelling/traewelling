<h4>{{__('stats.global')}}</h4>
<hr/>
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-4 text-center">
                <i class="fas fa-ruler fa-4x mt-1"></i>
            </div>
            <div class="col-8 text-center">
                <span class="font-weight-bold color-main fs-2">
                    {{number($globalStats->distance, 0)}} km
                </span>
                <br>
                <small class="text-muted">{{__('stats.global.distance')}}</small>
            </div>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-4 text-center">
                <i class="fas fa-clock fa-4x mt-1"></i>
            </div>
            <div class="col-8 text-center">
                <span class="font-weight-bold color-main fs-2">
                    {!! durationToSpan(secondsToDuration($globalStats->duration)) !!}
                </span>
                <br>
                <small class="text-muted">{{__('stats.global.duration')}}</small>
            </div>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-4 text-center">
                <i class="fas fa-users fa-4x mt-1"></i>
            </div>
            <div class="col-8 text-center">
                <span class="font-weight-bold color-main fs-2">
                    {{$globalStats->user_count}}x
                </span>
                <br>
                <small class="text-muted">{{__('stats.global.active')}}</small>
            </div>
        </div>
    </div>
</div>
<hr/>
<small class="text-muted">*{{strtr(__('stats.global.explain'), [
                                ':fromDate' => $from->format('d.m.Y'),
                                ':toDate' => $to->format('d.m.Y')
                            ])}}</small>
<hr/>
