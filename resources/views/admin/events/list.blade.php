@extends('layouts.admin')

@section('title', 'Aktuelle und zukünftige Veranstaltungen')

@section('content')
    <div class="card">
        <div class="card-body">
            @if($events->count() == 0)
                <p class="font-weight-bold text-danger">
                    Es sind aktuell keine aktuellen Veranstaltungen vorhanden. :(
                </p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Veranstalter</th>
                            <th>Beginn</th>
                            <th>Ende</th>
                            <th>Externe URL</th>
                            <th>Station</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $event)
                            <tr>
                                <td>{{$event->id}}</td>
                                <td>{{$event->name}}</td>
                                <td>{{$event->host}}</td>
                                <td>{{$event->begin->format('d.m.Y')}}</td>
                                <td>{{$event->end->format('d.m.Y')}}</td>
                                <td>{{$event->url}}</td>
                                <td>{{$event->getTrainstation()?->name}}</td>
                                <td>
                                    <a href="{{route('admin.events.edit', ['id' => $event->id])}}"
                                       class="btn btn-sm btn-primary">
                                        Bearbeiten
                                    </a>
                                </td>
                                <td>
                                    <form method="POST" action="{{route('admin.events.delete')}}">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$event->id}}"/>
                                        <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
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
