@php use Illuminate\Support\Facades\Date; @endphp
@extends('admin.layout')

@section('title', 'Event suggestions')

@section('content')
    <div class="card">
        <div class="card-body">
            @if($suggestions->count() === 0)
                <p class="font-weight-bold text-danger">
                    Nothing to do here! 🎉
                </p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Organizer</th>
                            <th>Begin</th>
                            <th>End</th>
                            <th>External URL</th>
                            @if(auth()->user()->hasRole('admin'))
                                <th>Suggesting user</th>
                            @endif
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suggestions as $eventSuggestion)
                            @if($eventSuggestion->user->id === auth()->id() && !auth()->user()->hasRole('admin'))
                                @continue
                            @endif

                            <tr class="{{$eventSuggestion->begin->isPast() ? 'table-danger' : ''}}">
                                <td>{{$eventSuggestion->name}}</td>
                                <td>{{$eventSuggestion->host}}</td>
                                <td>
                                    {{$eventSuggestion->begin->format('d.m.Y')}}
                                    @if($eventSuggestion->begin->isPast())
                                        <div class="spinner-grow text-danger" style="width: 1rem; height: 1rem;"></div>
                                    @elseif($eventSuggestion->begin->isBefore(Date::today()->addDays(3)))
                                        <div class="spinner-grow text-info" style="width: 1rem; height: 1rem;"></div>
                                    @endif
                                </td>
                                <td>{{$eventSuggestion->end->format('d.m.Y')}}</td>
                                <td>{{$eventSuggestion->url}}</td>
                                @if(auth()->user()->hasRole('admin'))
                                    <td>
                                        <a href="{{route('admin.users.user', ['id' => $eventSuggestion->user->id])}}">
                                            {{$eventSuggestion->user->name}}
                                        </a>
                                    </td>
                                @endif
                                <td class="text-end">
                                    <form method="POST" action="{{route('admin.events.suggestions.deny')}}">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$eventSuggestion->id}}"/>

                                        <div class="btn-group">
                                            @can('accept-events')
                                                <a class="btn btn-sm btn-success"
                                                   href="{{route('admin.events.suggestions.accept', ['id' => $eventSuggestion->id])}}">
                                                    Edit & accept
                                                </a>
                                            @endcan
                                            @can('deny-events')
                                                <x-event-rejection-button/>
                                            @endcan
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @if(!auth()->user()->hasRole('admin'))
            <div class="card-footer">
                <small>
                    You cannot see your own suggestions.
                    They are only visible to other event moderators and admins.
                </small>
            </div>
        @endif
    </div>
@endsection
