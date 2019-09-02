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

                <form method="GET" action="{{ route('export.csv') }}" class="row">
                    @csrf
                    <div class="col">
                        <label for="begin">Von</label>
                        <input name="begin" type="date" value="{{$begin_of_month}}" class="form-control">
                    </div>
                    <div class="col">
                        <label for="end">Bis</label>
                        <input name="end" type="date" value="{{$end_of_month}}" class="form-control">
                    </div>
                    <div class="col">
                        <label for="">&nbsp;</label>
                        <input type="submit" value="Exportieren" class="form-control btn btn-primary">
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection