@extends('layouts.app')

@section('title', 'RIS')

@php
    $createTripQuery = '';
    if (isset($station)) {
        $createTripQuery = '?from=' . $station->id;
    }
@endphp

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7" id="station-board-new">
                <Stationboard></Stationboard>

                @cannot('disallow-manual-trips')
                    <div class="text-center mt-4">
                        <hr/>
                        <p>
                            <i class="fa-solid fa-plus"></i>
                            {{__('missing-journey')}}
                        </p>
                        <a href="{{ route('trip.create') . $createTripQuery }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-plus"></i>
                            {{__('create-journey')}}
                        </a>
                    </div>
                @endcannot

                @if(isset($station) && auth()->user()?->hasRole('open-beta'))
                    @include('includes.station-infos')
                @endif
            </div>
        </div>
    </div>
@endsection
