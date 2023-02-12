@extends('admin.layout')

@section('title', 'Stats')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">{{__('admin.usage-board')}}</h5>
            <form class="row">
                <div class="col-6 col-md-4">
                    <label for="since">{{__('export.begin')}}:</label>
                    <input name="since" type="date" value="{{ $since?->toDateString() }}" class="form-control"/>
                </div>
                <div class="col-6 col-md-4">
                    <label for="until">{{__('export.end')}}:</label>
                    <input name="until" type="date" value="{{ $until?->toDateString() }}" class="form-control"/>
                </div>
                <div class="col-md-4">
                    <label for="btn">&nbsp;</label>
                    <input type="submit" id="btn-primary" value="{{__('admin.select-range')}}"
                           class="btn btn-primary form-control"/>
                </div>
            </form>

            <div class="row pt-4">
                <div class="col-md-4">
                    @include('admin.charts.statusesByDate')
                </div>
                <div class="col-md-4">
                    @include('admin.charts.userRegistration')
                </div>
                <div class="col-md-4">
                    @include('admin.charts.hafasPolylines')
                </div>
            </div>
        </div>
    </div>
@endsection
