@extends('layouts.app')

@section('title'){{__('stats')}} @endsection

@section('head')
    @parent
    <script src="{{ asset('js/stats.js') }}"></script>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h4>{{strtr(__('stats.personal'), [
                        ':fromDate' => $from->format('d.m.Y'),
                        ':toDate' => $to->format('d.m.Y')
                    ])}}</h4>
                <hr/>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        @include('stats.includes.chart_companies')
                    </div>
                    <div class="col-md-6 mb-4">
                        @include('stats.includes.chart_categories')
                    </div>
                    <hr/>
                    <div class="col-12 mb-4">
                        @include('stats.includes.chart_volume')
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                @include('stats.includes.global_cards')
            </div>
        </div>
    </div>
@endsection
