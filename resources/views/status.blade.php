@extends('layouts.app')

@section('title')
    Privacy
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @include('includes.status')
            </div>
        </div>
        @include('includes.edit-modal')
    </div><!--- /container -->
@endsection
