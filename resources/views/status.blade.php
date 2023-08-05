@extends('layouts.app')

@section('title', $title)
@section('canonical', route('statuses.get', ['id' => $status->id]))

@if($status->user->prevent_index)
    @section('meta-robots', 'noindex')
@else
    @section('meta-description', $status->description)
@endif

@section('head')
    @parent
    <meta property="og:title" content="{{ $title }}"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{ url('/status/'.$status->id)  }}"/>
    <meta property="og:image" content="{{ $image }}?{{ $status->user->updated_at->timestamp }}"/>
    <meta property="og:description" content="{{ $description }}"/>

    <meta name="twitter:card" content="summary"/>
    <meta name="twitter:site" content="@traewelling"/>
    <meta name="twitter:title" content="{{ $title }}"/>
    <meta name="twitter:description" content="{{ $description }}"/>
    <meta name="twitter:image" content="{{ $image }}?{{ $status->user->updated_at->timestamp }}"/>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <h2 class="fs-5">{{ userTime($status->trainCheckin->departure,__('dateformat.with-weekday')) }}</h2>
                @include('includes.status')

                @if(isset($status->trainCheckin->HafasTrip->last_refreshed) && \Illuminate\Support\Facades\Date::now()->isBefore($status->created_at->clone()->addDay()))
                    <small class="text-muted">
                        {{__('real-time-last-refreshed')}}
                        {{$status->trainCheckin->HafasTrip->last_refreshed->diffForHumans()}}
                    </small>
                @endif

            </div>
            <div class="col-md-4 col-lg-5">
                <div class="card mb-3">
                    <div class="card-body">
                        <!-- ToDo: make this beautiful -->
                        <b>Operator:</b>
                        {{$status->trainCheckin->HafasTrip->operator->name}}
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h3 class="fs-5 text-trwl">
                            <i class="fa-solid fa-list-ul"></i>
                            Stopovers
                        </h3>

                        @foreach($status->trainCheckin->stopovers as $stopover)
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('trains.stationboard', ['ibnr' => $stopover->station->ibnr, 'when' => $stopover->departure]) }}">
                                        {{$stopover->station->name}}
                                    </a>
                                </div>
                                <div class="col-6 text-end">
                                    {{ userTime($stopover->departure) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($status->trainCheckin->alsoOnThisConnection->count() > 0)
                    <div class="card">
                        <div class="card-body">
                            <h3 class="fs-5 text-trwl">
                                <i class="fa-solid fa-users-between-lines"></i>
                                Also on this connection
                            </h3>
                            <div class="row">
                                @foreach($status->trainCheckin->alsoOnThisConnection as $otherStatus)
                                    <!-- TODO: bring image to the center - i'm too dumb for this :c -->
                                    <div class="col">
                                        <div class="image-box pe-0 d-lg-flex" style="width: 4em; height: 4em;">
                                            <a href="{{ route('statuses.get', ['id' => $otherStatus->id]) }}">
                                                <img
                                                    src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($otherStatus->user) }}"
                                                    alt="{{$otherStatus->user->username}}"
                                                    loading="lazy"
                                                    decoding="async"
                                                    data-mdb-toggle="tooltip"
                                                    data-mdb-placement="bottom"
                                                    title="{{$otherStatus->user->name}} ({{$otherStatus->trainCheckin->originStation->name}} - {{$otherStatus->trainCheckin->destinationStation->name}})"
                                                />
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @if(auth()->check() && auth()->user()->id == $status->user_id)
            @include('includes.edit-modal')
            @include('includes.delete-modal')
        @endif
    </div>
@endsection
