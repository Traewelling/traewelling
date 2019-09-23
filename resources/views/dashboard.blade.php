@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('content')
@include('includes.station-autocomplete')
<div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                    <?php $day = ""; ?>
                    @foreach($statuses as $status)
                        <?php $newDay = date('Y-m-d', strtotime($status->trainCheckin->departure)); ?>
                        @if($newDay != $day)
                            <?php
                            $day = $newDay;
                            $dtObj = new \DateTime($status->trainCheckin->departure);
                            ?>
                            <h5 class="mt-4">{{__($dtObj->format('l')) }}, {{ $dtObj->format('j') }}. {{__($dtObj->format('F')) }} {{ $dtObj->format('Y') }}</h5>
                        @endif

                        @include('includes.status')
                @endforeach
            </div>
        </div>
    <div class="row justify-content-center mt-5">
        {{ $statuses->links() }}
    </div>
@include('includes.edit-modal')
@include('includes.delete-modal')
</div><!--- /container -->
@endsection
