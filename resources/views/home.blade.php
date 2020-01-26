@extends('layouts.app')
@section('content')
<!--
    Abandoned?
-->

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                        <div id="remote">
                            <input id="station-autocomplete" class="form-control" type="text" placeholder="Bahnhof">
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
