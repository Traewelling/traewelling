@extends(!auth()->user()?->hasRole('open-beta') ? 'layouts.app' : 'layouts.tailwind-vue-layout')

@section('title', 'RIS')

@php
    $createTripQuery = '';
    if (isset($station)) {
        $createTripQuery = '?from=' . $station->id;
    }
@endphp

@section('content')
    <div id="vue-content">
        <Leaderboard></Leaderboard>
    </div>
@endsection
