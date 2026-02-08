@extends('layouts.app')

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
