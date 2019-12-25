@extends('layouts.app')

@section('title')
    Events
@endsection

@section('content')
    <div class="container">

        <ul class="nav nav-tabs" id="mytab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="upcoming-tab" data-toggle="tab" href="#upcoming" role="tab" aria-controls="upcoming" aria-selected="true">{{ __('events.upcoming') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="live-tab" data-toggle="tab" href="#live" role="tab" aria-controls="live" aria-selected="true">{{ __('events.live') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="past-tab" data-toggle="tab" href="#past" role="tab" aria-controls="past" aria-selected="true">{{ __('events.past') }}</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('events.newform') }}" class="nav-link btn-primary">{{ __('events.new') }}</a>
            </li>
        </ul>

        <div class="tab-content pt-4" id="myTabContent" style="border-top: 1px solid #dee2e6;">
            <div class="tab-pane show active" role="tabpanel" id="upcoming" aria-labelledby="upcoming-tab">
                <div class="row">
                    @foreach($upcoming as $event)
                        <div class="col-md-6 col-lg-4">
                            @include("includes.event")
                        </div>
                    @endforeach
                </div>
                
                @if($upcoming->count() == 0)
                --none--
                @endif
            </div>

            <div class="tab-pane" role="tabpanel" id="live" aria-labelledby="live-tab">
                <div class="row">
                    @foreach($live as $event)
                        <div class="col-md-6 col-lg-4">
                            @include("includes.event")
                        </div>
                    @endforeach
                </div>
                
                @if($live->count() == 0)
                --none--
                @endif
            </div>

            <div class="tab-pane" role="tabpanel" id="past" aria-labelledby="past-tab">
                <div class="row">
                    @foreach($past as $event)
                        <div class="col-md-6 col-lg-4">
                            @include("includes.event")
                        </div>
                    @endforeach
                </div>
                
                @if($past->count() == 0)
                --none--
                @endif
            </div>
        </div>
    </div>
@endsection
