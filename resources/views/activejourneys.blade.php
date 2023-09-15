@extends('layouts.app')

@section('title', __('menu.active'))

@section('meta-robots', 'index')
@section('meta-description', __('description.en-route'))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="fs-4">{{ __('menu.active') }}</h1>
            </div>
            <div class="col-md-6 mb-4" id="activeJourneys">
                <active-journey-map map-provider="{{ Auth::user()->mapprovider ?? "default" }}">
                </active-journey-map>

                <div class="row text-center fs-5 mt-3">
                    <div class="col mb-3">
                        <i class="fa-solid fa-train"></i>
                        {{$statuses->count()}}
                        {{trans_choice('active-journeys', $statuses->count())}}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                @if($statuses->count() === 0)
                    <div class="alert alert-danger text-center">
                        <strong class="fs-4">{{__('empty-en-route')}}</strong>
                    </div>
                @endif

                @include('includes.statuses', ['statuses' => $statuses, 'showDates' => false])
            </div>
        </div>
    </div>

    @include('includes.edit-modal')
    @include('includes.delete-modal')
@endsection
