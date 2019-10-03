@extends('layouts.app')

@section('title')
    Imprint
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1><i class="fa fa-save"></i> Exportieren</h1>
                <p class="lead">Hier kannst du deine Zugfahrten aus der Datenbank als CSV exportieren! Die einzelnen Daten sind durch einen Tabstopp voneinander getrennt.</p>

                <form method="GET" action="{{ route('export.csv') }}">
                    @csrf
                <div class="row">
                    <div class="col">
                        <label for="begin">Von</label>
                        <input name="begin" type="date" value="{{$begin_of_month}}" class="form-control">
                    </div>
                    <div class="col">
                        <label for="end">Bis</label>
                        <input name="end" type="date" value="{{$end_of_month}}" class="form-control">
                    </div>
                </div>
                <div class="row pt-2">
                    <div class="col">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="private-trips" name="private-trips" value="true" checked>
                            <label class="custom-control-label" for="private-trips">Private Reisen</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="business-trips" name="business-trips" value="true" checked>
                            <label class="custom-control-label" for="business-trips">Geschäftliche Reisen</label>
                        </div>
                    </div>
                    <div class="col">
                        <input type="submit" value="Exportieren" class="btn btn-primary m-0">
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection