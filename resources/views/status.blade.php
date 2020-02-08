@extends('layouts.app')
<?php
$title = __('status.ogp-title', ['name' => $status->user->username]);
$description = trans_choice('status.ogp-description', preg_match('/\s/', $status->trainCheckin->getHafasTrip->linename), [
    'linename' => $status->trainCheckin->getHafasTrip->linename,
    'distance' => $status->trainCheckin->distance,
    'destination' => $status->trainCheckin->getDestination->name,
    'origin' => $status->trainCheckin->getOrigin->name
]);
$image = route('account.showProfilePicture', ['username' => $status->user->username]);
?>

@section('title'){{ $title }}@endsection

@section('metadata')
    <meta property="og:title" content="{{ $title }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/status/'.$status->id)  }}" />
    <meta property="og:image" content="{{ $image }}" />
    <meta property="og:description" content="{{ $description }}" />

    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@traewelling" />
    <meta name="twitter:title" content="{{ $title }}" />
    <meta name="twitter:description" content="{{ $description }}" />
    <meta name="twitter:image" content="{{ $image }}" />
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php
                $dtObj = new \DateTime($status->trainCheckin->departure);
                ?>
                <h5>{{__("dates." . $dtObj->format('l')) }}, {{ $dtObj->format('j') }}. {{__("dates." . $dtObj->format('F')) }} {{ $dtObj->format('Y') }}</h5>
                @include('includes.status')
            </div>
        </div>
        @include('includes.edit-modal')
        @include('includes.delete-modal')
    </div><!--- /container -->
@endsection
