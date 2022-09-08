@extends('layouts.app')

@section('title', $pageTitle)
@section('canonical', route('statuses.get', ['id' => $status->id]))

@if($status->user->prevent_index)
    @section('meta-robots', 'noindex')
@else
    @section('meta-description', $pageDescription)
@endif

@section('head')
    @parent
    <meta property="og:title" content="{{ $pageTitle }}"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{ url('/status/' . $status->id)  }}"/>
    <meta property="og:image" content="{{ $image }}?{{ $status->user->updated_at->timestamp }}"/>
    <meta property="og:description" content="{{ $pageDescription }}"/>

    <meta name="twitter:card" content="summary"/>
    <meta name="twitter:site" content="@traewelling"/>
    <meta name="twitter:title" content="{{ $pageTitle }}"/>
    <meta name="twitter:description" content="{{ $pageDescription }}"/>
    <meta name="twitter:image" content="{{ $image }}?{{ $status->user->updated_at->timestamp }}"/>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <h2 class="fs-5">{{ $status->locationCheckin->arrival->isoFormat(__('dateformat.with-weekday')) }}</h2>
                @include('includes.status-location')
            </div>
        </div>
        @if(auth()->check() && auth()->user()->id == $status->user_id)
            <!-- ToDo: Edit -->
            <!-- ToDo: Delete -->
        @endif
    </div>
@endsection
