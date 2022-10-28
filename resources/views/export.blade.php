@extends('layouts.app')

@section('title', __('export.title'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1><i class="fa fa-save"></i> {{__('export.title')}}</h1>
                <p class="lead">{{__('export.lead')}}</p>

                <form method="POST" action="{{ route('export.generate') }}">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="form-floating">
                                        <input name="from" id="from" type="date" value="{{$begin_of_month}}"
                                               class="form-control"/>
                                        <label for="from">{{__('export.begin')}}</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating">
                                        <input name="until" id="until" type="date" value="{{$end_of_month}}"
                                               class="form-control"/>
                                        <label for="until">{{__('export.end')}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="col text-end">
                                    <span>{{__('export.submit')}}: </span>

                                    <div class="btn-group">
                                        @foreach(['pdf' => 'fa-regular fa-file-pdf', 'csv' => 'fa-solid fa-file-csv', 'json' => 'fa-regular fa-file-code'] as $filetype => $icon)
                                            <button type="submit" class="btn btn-primary" name="filetype"
                                                    value="{{$filetype}}">
                                                <i class="{{$icon}}"></i>&nbsp;&nbsp;{{$filetype}}
                                            </button>
                                        @endforeach
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
