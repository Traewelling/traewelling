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

    <div class="card">
        <div class="card-body">
            <a href="{{route('admin.events.suggestions')}}" class="btn btn-sm btn-info">
                User Suggestions
            </a>
            <a href="{{route('admin.events.create')}}" class="btn btn-sm btn-success float-end">
                <i class="fas fa-plus" aria-hidden="true"></i>
                Create
            </a>
            @if($events->count() === 0)
                <p class="font-weight-bold text-danger">
                    There are currently no events available. :(
                </p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Veranstalter</th>
                                <th>Checkin Beginn</th>
                                <th>Checkin Ende</th>
                                <th>Event Beginn</th>
                                <th>Event Ende</th>
                                <th>Externe URL</th>
                                <th>Station</th>
                                <th>Approved by</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                                <tr>
                                    <td>{{$event->id}}</td>
                                    <td>
                                        <a href="{{route('event', ['slug' => $event->slug])}}"
                                           target="{{$event->slug}}">
                                            {{$event->name}}
                                        </a>
                                    </td>
                                    <td>{{$event->host}}</td>
                                    <td>{{$event->begin->format('d.m.Y')}}</td>
                                    <td>{{$event->end->format('d.m.Y')}}</td>
                                    <td>{{$event->event_start?->format('d.m.Y')}}</td>
                                    <td>{{$event->event_end?->format('d.m.Y')}}</td>
                                    <td>{{$event->url}}</td>
                                    <td>{{$event->station?->name}}</td>
                                    <td>
                                        @if($event->approved_by)
                                            <a href="{{ route('admin.users.user', ['id' => $event->approved_by]) }}">
                                                {{ '@'.$event->approvedBy?->username }}
                                            </a>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{route('admin.events.delete')}}">
                                            @csrf
                                            <input type="hidden" name="id" value="{{$event->id}}"/>
                                            <div class="btn-group">
                                                <a href="{{route('admin.events.edit', ['id' => $event->id])}}"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{$events->links()}}
            @endif
        </div>
    </div>
@endsection
