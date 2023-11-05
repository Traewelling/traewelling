@extends('layouts.app')

@section('title', $title)
@section('canonical', route('status', ['id' => $status->id]))

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
                @if(auth()->user()->experimental ?? false)
                    <div id="checkin-success-helper">
                        <checkin-success-helper></checkin-success-helper>
                    </div>
                @endif
                <h2 class="fs-5">{{ userTime($status->trainCheckin->departure,__('dateformat.with-weekday')) }}</h2>
                @include('includes.status')

                @if(isset($status->trainCheckin->HafasTrip->last_refreshed) && \Illuminate\Support\Facades\Date::now()->isBefore($status->created_at->clone()->addDay()))
                    <small class="text-muted">
                        {{__('real-time-last-refreshed')}}
                        {{$status->trainCheckin->HafasTrip->last_refreshed->diffForHumans()}}
                    </small>
                @endif

                @foreach($status->tags as $tag)
                    @can('view', $tag)
                        <span class="badge bg-trwl">
                            @if($tag->key === 'trwl:seat')
                                <i class="fa-solid fa-couch me-1"></i>
                            @elseif($tag->key === 'trwl:ticket')
                                <i class="fa-solid fa-qrcode me-1"></i>
                            @elseif($tag->key === 'trwl:wagon')
                                <!-- TODO: Icon? -->
                            @elseif($tag->key === 'trwl:travel_class')
                                <!-- TODO: Icon? -->
                            @elseif($tag->key === 'trwl:locomotive_class')
                                <!-- TODO: Icon? -->
                            @elseif($tag->key === 'trwl:wagon_class')
                                <!-- TODO: Icon? -->
                            @elseif($tag->key === 'trwl:role')
                                <i class="fa-solid fa-briefcase me-1"></i>
                            @endif
                            {{$tag->value}}
                        </span>
                    @endcan
                @endforeach
            </div>
        </div>
        @if(auth()->check() && auth()->user()->id == $status->user_id)
            @include('includes.edit-modal')
            @include('includes.delete-modal')
        @endif
    </div>
@endsection
