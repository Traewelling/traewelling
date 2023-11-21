@php use App\Enum\EventRejectionReason; @endphp
@php use Illuminate\Support\Facades\Date; @endphp
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
                            <th>Organizer</th>
                            <th>Begin</th>
                            <th>End</th>
                            <th>External URL</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suggestions as $event)
                            <tr class="{{$event->begin->isPast() ? 'table-danger' : ''}}">
                                <td>{{$event->name}}</td>
                                <td>{{$event->host}}</td>
                                <td>
                                    {{$event->begin->format('d.m.Y')}}
                                    @if($event->begin->isPast())
                                        <div class="spinner-grow text-danger" style="width: 1rem; height: 1rem;"></div>
                                    @elseif($event->begin->isBefore(Date::today()->addDays(3)))
                                        <div class="spinner-grow text-info" style="width: 1rem; height: 1rem;"></div>
                                    @endif
                                </td>
                                <td>{{$event->end->format('d.m.Y')}}</td>
                                <td>{{$event->url}}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{route('admin.events.suggestions.deny')}}">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$event->id}}"/>

                                        <div class="btn-group">
                                            @can('accept-events')
                                                <a class="btn btn-sm btn-success"
                                                   href="{{route('admin.events.suggestions.accept', ['id' => $event->id])}}">
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
    </div>
@endsection
