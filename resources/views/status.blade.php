@extends('layouts.app')

@section('title', $title)
@section('canonical', route('statuses.get', ['id' => $status->id]))

@if($status->user->prevent_index)
    @section('meta-robots', 'noindex')
@else
    @section('meta-description', __('description.status', [
        'username' => $status->user->name,
        'origin' => $status->trainCheckin->Origin->name .
                    ($status->trainCheckin->Origin->rilIdentifier ?
                    ' (' .$status->trainCheckin->Origin->rilIdentifier . ')' : ''),
        'destination' => $status->trainCheckin->Destination->name .
                         ($status->trainCheckin->Destination->rilIdentifier ?
                         ' (' .$status->trainCheckin->Destination->rilIdentifier . ')' : ''),
        'date' => $status->trainCheckin->departure->isoFormat(__('datetime-format')),
        'lineName' => $status->trainCheckin->HafasTrip->linename
    ]))
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
                <h5>{{ $status->trainCheckin->departure->isoFormat(__('dateformat.with-weekday')) }}</h5>
                @include('includes.status')
            </div>
        </div>
        @include('includes.edit-modal')
        @include('includes.delete-modal')
    </div>
@endsection
