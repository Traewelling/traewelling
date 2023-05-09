@extends('admin.layout')

@section('title', 'Veranstaltungsvorschläge')

@section('content')
    <div class="card">
        <div class="card-body">
            @if($suggestions->count() == 0)
                <p class="font-weight-bold text-danger">Es sind aktuell keine Vorschläge vorhanden. :(</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Veranstalter</th>
                            <th>Beginn</th>
                            <th>Ende</th>
                            <th>Externe URL</th>
                            <th>Vorschlagender Nutzer</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suggestions as $event)
                            <tr>
                                <td>{{$event->name}}</td>
                                <td>{{$event->host}}</td>
                                <td>{{$event->begin->format('d.m.Y')}}</td>
                                <td>{{$event->end->format('d.m.Y')}}</td>
                                <td>{{$event->url}}</td>
                                <td>{{$event->user?->username}}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{route('admin.events.suggestions.deny')}}">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$event->id}}"/>

                                        <div class="btn-group">
                                            <a class="btn btn-sm btn-success"
                                               href="{{route('admin.events.suggestions.accept', ['id' => $event->id])}}">
                                                Edit & accept
                                            </a>
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Decline
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
