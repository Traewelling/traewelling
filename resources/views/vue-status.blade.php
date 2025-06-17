@extends('layouts.app')

@section('title', __('status.ogp-title', ['name' => '']))

@section('content')
    <div class="container">
        <div id="vue-content">
            <single-status></single-status>
        </div>
    </div>
@endsection
