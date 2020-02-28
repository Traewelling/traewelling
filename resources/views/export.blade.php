@extends('layouts.app')

@section('title')
    {{__('export.title')}}
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1><i class="fa fa-save"></i> {{__('export.title')}}</h1>
                <p class="lead">{{__('export.lead')}}</p>

                <form method="GET" action="{{ route('export.generate') }}">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <label for="begin">{{__('export.begin')}}</label>
                                    <input name="begin" type="date" value="{{$begin_of_month}}" class="form-control">
                                </div>
                                <div class="col">
                                    <label for="end">{{__('export.end')}}</label>
                                    <input name="end" type="date" value="{{$end_of_month}}" class="form-control">
                                </div>
                            </div>
                            <div class="row pt-2">
                                <input type="checkbox" class="custom-control-input" id="private-trips" name="private-trips" value="true" checked>
                                <input type="checkbox" class="custom-control-input" id="business-trips" name="business-trips" value="false" checked>

                                <div class="col text-right">
                                    <div class="btn-group">
                                        <button id="export_submit" type="submit" class="btn btn-primary" name="filetype" value="pdf">{{ __('export.submit') }}</button>
                                        <button type="button" class="btn btn-primary dropdown-toggle px-3" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <div class="dropdown-menu">
                                            <button type="submit" name="filetype" value="csv" class="dropdown-item" href="#">.csv</button>
                                            <button type="submit" name="filetype" value="json" class="dropdown-item" href="#">.json</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
