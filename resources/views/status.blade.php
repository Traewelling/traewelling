@extends('layouts.app')

@section('title')
    Privacy
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
