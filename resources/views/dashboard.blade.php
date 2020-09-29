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
                    @if($loop->first || !$status->trainCheckin->departure->isSameDay($statuses[$loop->index - 1]->trainCheckin->departure))
                        <h5 class="mt-4">{{$status->trainCheckin->departure->isoFormat('dddd, DD. MMMM YYYY')}}</h5>
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
