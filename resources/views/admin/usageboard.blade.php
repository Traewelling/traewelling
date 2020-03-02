@extends('layouts.admin')

@section('title')
    {{__('admin.usage')}}
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="card-title">{{__('admin.usage-board')}}</div>
        </div>
        <div class="card-body">
            <form method="get" class="row">
                @csrf
                <div class="col-6 col-md-4">
                    <label for="begin">{{__('export.begin')}}:</label>
                    <input name="begin" type="date" value="{{ $begin }}" class="form-control">
                </div>
                <div class="col-6 col-md-4">
                    <label for="end">{{__('export.end')}}:</label>
                    <input name="end" type="date" value="{{ $end }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="btn">&nbsp;</label>
                    <input type="submit" id="btn btn-primary" value="{{__('admin.select-range')}}"
                           class="btn-primary form-control">
                </div>
            </form>

            <div class="row pt-4">
                <div class="col-md-3">
                    <h5 class="text-center pt-3">{{__('admin.checkins')}}</h5>
                    <canvas
                        id="statusesByDateCanvas"
                        class="date-canvas"
                        data-labels='{!! json_encode($dates) !!}'
                        data-json='{!! json_encode($statusesByDay) !!}'
                        data-title="# {{__('admin.statuses')}}"
                        data-keys='occurs'
                        height="300"
                    ></canvas>
                </div>
                <div class="col-md-3">
                    <h5 class="text-center pt-3">{{__('admin.registered-users')}}</h5>
                    <canvas
                        id="userRegistrationCanvas"
                        class="date-canvas"
                        data-labels='{!! json_encode($dates) !!}'
                        data-json='{!! json_encode($userRegistrationsByDay) !!}'
                        data-title="# {{__('admin.new-users')}}"
                        data-keys='occurs'
                        height="300"
                    ></canvas>
                </div>
                {{--    </div>--}}
                {{--    <div class="row pt-4">--}}
                <div class="col-md-3">
                    <h5 class="text-center pt-3">{{__('admin.hafas-entries-by-type')}}</h5>
                    <canvas
                        id="transportTypesCanvas"
                        class="date-canvas"
                        data-labels='{!! json_encode($dates) !!}'
                        data-json='{!! json_encode($hafasTripsByDay) !!}'
                        data-title="{{__('transport_types.tram')}},{{__('transport_types.bus')}},{{__('transport_types.subway')}},{{__('transport_types.suburban')}},{{__('transport_types.ferry')}},{{__('transport_types.regional')}},{{__('transport_types.regional')}},{{__('transport_types.express')}},{{__('transport_types.national')}},{{__('transport_types.nationalExpress')}}"
                        data-keys='tram,bus,subway,suburban,ferry,regional,regionalExp,express,national,nationalExpress'
                        height="300"
                    ></canvas>
                </div>
                <div class="col-md-3">
                    <h5 class="text-center pt-3">{{__('admin.hafas-entries-by-polylines')}}</h5>
                    <canvas
                        id="hafasPolylinesCanvas"
                        class="date-canvas"
                        data-labels='{!! json_encode($dates) !!}'
                        data-json='{!! json_encode($hafasTripsByDay) !!}'
                        data-title="HafasTrips,Polylines"
                        data-keys='hafas,polylines'
                        height="300"
                    ></canvas>
                </div>
        </div>
    </div>
</div>

@endsection
