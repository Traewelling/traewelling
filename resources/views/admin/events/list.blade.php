@extends('admin.layout')

@section('title', 'Events' . (request()->has('query') ? ' - Search for "' . request()->get('query') . '"' : ''))

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET">
                <input type="text" class="form-control" name="query" placeholder="Search for events by title"
                       value="{{request()->get('query')}}"
                />
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            @if(auth()->user()->can('accept-events') || auth()->user()->can('deny-events'))
                <a href="{{route('admin.events.suggestions')}}" class="btn btn-sm btn-info">
                    User Suggestions
                </a>
            @endif
            @can('create-events')
                <a href="{{route('admin.events.create')}}" class="btn btn-sm btn-success float-end">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                    Create
                </a>
            @endcan
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h2>Future Events</h2>
            @include("admin.events.includes.table", ["events" => $events_future])
        </div>
    </div>
    <hr />
    <div class="card mb-3">
        <div class="card-body">
            <h2>Current Events</h2>
            @include("admin.events.includes.table", ["events" => $events_current])
        </div>
    </div>
    <hr />
    <div class="card mb-3">
        <div class="card-body">
            <h2>Past Events</h2>
            @include("admin.events.includes.table", ["events" => $events_past])
        </div>
    </div>
@endsection
