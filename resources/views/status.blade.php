@extends('layouts.app')

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
                <h5>{{ $status->trainCheckin->departure->isoFormat('dddd, DD. MMMM YYYY') }}</h5>
                @include('includes.status')
            </div>
        </div>
        @include('includes.edit-modal')
        @include('includes.delete-modal')
    </div><!--- /container -->
@endsection
