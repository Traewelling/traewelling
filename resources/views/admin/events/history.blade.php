@extends('admin.layout')

@section('title', 'ActivityLog of Event#' . $eventId)

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @include('components.activity-log-table', ['activities' => $activities])
                    {{$activities->appends(request()->input())->links()}}
                </div>
            </div>
        </div>
    </div>
@endsection
