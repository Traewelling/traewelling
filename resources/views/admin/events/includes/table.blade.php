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
                <tr data-id="{{$event->id}}">
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
                            <button class="btn btn-sm btn-danger btn-delete-event"
                                    data-id="{{$event->id}}"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{$events->links()}}

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.btn-delete-event').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    if (!confirm('Are you sure you want to delete this event?')) {
                        return;
                    }
                    fetch(`/api/v1/events/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                    })
                        .then(response => {
                            if (response.ok) {
                                // remove the row from the table
                                const row = document.querySelector(`tr[data-id='${id}']`);
                                row && row.remove();
                                notyf.success('Event deleted successfully.');
                            } else {
                                return response.json().then(err => Promise.reject(err));
                            }
                        })
                        .catch(error => {
                            console.error('Delete failed:', error);
                            notyf.error('Failed to delete the event. Please try again.');
                        });
                });
            });
        });
    </script>
@endif
