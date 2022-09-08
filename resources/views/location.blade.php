@extends('layouts.app')

@section('title', $location->name)
@section('canonical', route('location', ['slug' => $location->slug]))

@section('content')
    <div class="px-4 py-5 mt-n4"
         style="background-image: url({{url('/images/covers/profile-background.png')}});background-position: center;background-color: #c5232c">
        <div class="container" id="event-header">
            <div class="row justify-content-center">
                <div class="text-white col-md-8 col-lg-7">
                    <h1 class="card-title font-bold">
                        <strong>{{$location->name}}</strong>
                    </h1>
                    <h2 class="h2-responsive">
                            <span class="font-weight-bold ps-sm-2">
                                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                {{$location->address_street}}, {{$location->address_zip}} {{$location->address_city}}
                            </span>
                    </h2>
                    @isset($event->station)
                        <h2 class="h2-responsive">
                            <span class="font-weight-bold"><i class="fa fa-train"></i></span>
                            <span class="font-weight-bold">
                                 <a href="{{route('trains.stationboard', ['provider' => 'train', 'station' => $event->station->ibnr])}}"
                                    class="text-white">
                                    {{$event->station->name}}
                                 </a>
                            </span>
                        </h2>
                    @endisset
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7 pt-3" id="activeJourneys">
                @foreach($checkins as $checkin)
                    @include('includes.status-location', ['status' => $checkin->status])
                @endforeach
            </div>
        </div>

        <div class="row justify-content-center mt-5">
            {{ $checkins->links() }}
        </div>
    </div>

    @include('includes.edit-modal')
    @include('includes.delete-modal')
@endsection
