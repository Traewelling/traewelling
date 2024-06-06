@php use App\Models\Station; @endphp
<div class="card status">
    <div class="card-header">
        <a href="{{ route('events.show', ['slug' => $event->slug]) }}">{{ $event->name }}</a>
    </div>
    <div class="card-body">
        <table class="table mb-0">
            <tr>
                <th class="ps-0">Hashtag:</th>
                <td class="text-break"><code>#{{ $event->hashtag }}</code></td>
            </tr>
            <tr>
                <th class="ps-0">Host:</th>
                <td class="text-break">{{ $event->host }}</td>
            </tr>
            <tr>
                <th class="ps-0">URL:</th>
                <td class="text-break"><a href="{{ $event->url }}">{{ $event->url }} <i class="fas fa-link"></i></a>
                </td>
            </tr>
            <tr>
                <th class="ps-0">Beginn:</th>
                <td class="text-break">{{ $event->checkin_start->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <th class="ps-0">Ende:</th>
                <td class="text-break">{{ $event->checkin_end->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <th class="ps-0">Station:</th>
                <td class="text-break">{{ Station::find($event->trainstation)->name }}</td>
            </tr>
        </table>
    </div>
    <div class="card-footer">
        <ul class="list-inline">
            <li class="list-inline-item">
                <a href="{{ route('events.show', ['slug' => $event->slug]) }}">
                    <i class="fas fa-edit"></i>
                </a>
            </li>
            <li class="list-inline-item">
                <a href="#" role="button" data-mdb-toggle="modal" data-mdb-target="#delete-modal-{{ $event->id }}">
                    <i class="fas fa-trash"></i>
                </a>
            </li>
            <li class="list-inline-item">
                <a href="{{ route('event', ['slug' => $event->slug]) }}">
                    <i class="fas fa-list"></i>
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="delete-modal-{{ $event->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__('modals.deleteEvent-title')}}</h4>
                <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {!! __('modals.deleteEvent-body', ['name' => $event->name]) !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-mdb-dismiss="modal">{{__('menu.abort')}}</button>
                <a href="{{ URL::signedRoute('events.delete', ['slug' => $event->slug]) }}" class="btn btn-danger"
                   id="modal-delete">{{__('modals.delete-confirm')}}</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
