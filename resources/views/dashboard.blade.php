@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('content')
    @include('includes.station-autocomplete')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @include('includes.statuses', ['statuses' => $statuses, 'showDates' => true])
            </div>
        </div>
        <div class="row justify-content-center mt-5">
            {{ $statuses->links() }}
        </div>
        @include('includes.edit-modal')
        @include('includes.delete-modal')
    </div><!--- /container -->
@endsection
