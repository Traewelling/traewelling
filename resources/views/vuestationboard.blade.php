@extends('layouts.app')

@section('title', 'RIS')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7" id="station-board-new">
                <Stationboard></Stationboard>

                @if(auth()->user()->hasRole('open-beta') && !auth()->user()->can('disallow-manual-trips'))
                    <div class="text-center mt-4">
                        <hr/>
                        <p>
                            <span class="badge text-bg-info">Beta</span>
                            {{__('missing-journey')}}
                        </p>
                        <a href="{{ route('trip.create') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-plus"></i>
                            {{__('create-journey')}}
                        </a>
                    </div>
                @endif

                @if(isset($station) && auth()->user()?->hasRole('open-beta'))
                    @include('includes.station-infos')
                @endif
            </div>
        </div>
    </div>
@endsection
