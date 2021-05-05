@extends('layouts.app')

@section('title')Statistiken @endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <h4>Persönliche Statistiken vom {{$from->format('d.m.Y')}} bis {{$to->format('d.m.Y')}}</h4>
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
            <div class="col-md-4">
                @include('stats.includes.global_cards')
            </div>
        </div>
    </div>
@endsection
