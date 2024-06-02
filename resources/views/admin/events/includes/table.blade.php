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
                        <td>{{$event->checkin_start->format('d.m.Y')}}</td>
                        <td>{{$event->checkin_end->format('d.m.Y')}}</td>
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
                                    @can('view event history')
                                        <a href="{{route('admin.activity', ['subject_type' => $event::class, 'subject_id' => $event->id])}}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-history"></i>
                                        </a>
                                    @endcan
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
