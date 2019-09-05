@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('content')
@include('includes.station-autocomplete')
<div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @foreach($statuses as $status)
                    @include('includes.status')
                @endforeach
            </div>
        </div>
@include('includes.edit-modal')
</div><!--- /container -->
@endsection
