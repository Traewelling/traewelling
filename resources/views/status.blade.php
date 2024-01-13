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
                @if(auth()->check() && auth()->user()->hasRole('open-beta'))
                    <div id="checkin-success-helper">
                        <checkin-success-helper></checkin-success-helper>
                    </div>
                @endif
                <h2 class="fs-5">{{ userTime($status->checkin->departure,__('dateformat.with-weekday')) }}</h2>
                @include('includes.status')
                <div id="tag-helper">
                    <tag-helper
                        :status-id="{{ $status->id }}"
                        @can('update', $status)
                            :editable="true"
                        @endcan
                    ></tag-helper>
                </div>

                @if(isset($status->checkin->trip->last_refreshed) && \Illuminate\Support\Facades\Date::now()->isBefore($status->created_at->clone()->addDay()))
                    <hr/>
                    <small class="text-muted">
                        {{__('real-time-last-refreshed')}}
                        {{$status->checkin->trip->last_refreshed->diffForHumans()}}
                    </small>
                @endif
            </div>
        </div>
        @if(auth()->check() && auth()->user()->id == $status->user_id)
            @include('includes.edit-modal')
            @include('includes.delete-modal')
        @endif
    </div>
@endsection
