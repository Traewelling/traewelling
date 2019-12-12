@extends('layouts.app')

@section('title')
    Status-Detail
@endsection

@section('metadata')
    <meta property="og:title" content="{{ __('status.ogp-title', ['name' => $status->user->username, 'destination' => $status->trainCheckin->getDestination->name, 'origin' => $status->trainCheckin->getOrigin->name]) }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/status/'.$status->id)  }}" />
    <meta property="og:image" content="{{ url('account.showProfilePicture', ['username' => $status->user->username]) }}" />
    <meta property="og:description" content="{{ $status->trainCheckin->distance . trans_choice('status.ogp-description', preg_match('/\s/', $status->trainCheckin->getHafasTrip->linename), ['linename' => $status->trainCheckin->getHafasTrip->linename]) }}" />
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
