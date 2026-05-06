@extends('layouts.app')

@section('title', __('checkin.duplicates.title'))

@section('content')
    <div class="container">
        <div id="vue-content">
            <vue-duplicate-checkins></vue-duplicate-checkins>
        </div>
    </div>
@endsection
