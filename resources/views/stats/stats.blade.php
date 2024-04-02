@extends('layouts.app')

@section('title', __('stats'))

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
                        @include('stats.includes.chart_purpose')
                    </div>
                    <div class="col-md-6 mb-4">
                        @include('stats.includes.chart_categories')
                    </div>
                    <div class="col-md-6 mb-4">
                        @include('stats.includes.chart_companies')
                    </div>
                    <hr/>
                    <div class="col-12 mb-4">
                        @include('stats.includes.chart_volume')
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                @include('stats.includes.global_cards')

                @if(auth()->check() && auth()->user()->hasRole('open-beta'))
                    <h4>
                        {{__('experimental-features')}}
                    </h4>
                    <ul>
                        @if(auth()->check() && auth()->user()->hasRole('closed-beta'))
                            <li>
                                <a href="{{route('stats.stations')}}">
                                    {{__('stats.stations.description')}}
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{route('stats.daily', ['dateString' => today()->toDateString()])}}">
                                {{__('stats.daily.description')}}
                            </a>
                        </li>
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection
