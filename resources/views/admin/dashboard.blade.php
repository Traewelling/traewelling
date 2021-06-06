@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="card-title">{{__('admin.usage-board')}}</div>
        </div>
        <div class="card-body">
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
                    <input type="submit" id="btn btn-primary" value="{{__('admin.select-range')}}"
                           class="btn-primary form-control"/>
                </div>
            </form>

            <div class="row pt-4">
                <div class="col-md-3">
                    @include('admin.charts.statusesByDate')
                </div>
                <div class="col-md-3">
                    @include('admin.charts.userRegistration')
                </div>
                <div class="col-md-3">
                    @include('admin.charts.transportTypes')
                </div>
                <div class="col-md-3">
                    @include('admin.charts.hafasPolylines')
                </div>
            </div>
        </div>
    </div>
@endsection
