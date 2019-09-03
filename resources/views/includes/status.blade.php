<div class="card status mt-3" data-statusid="{{ $status->id }}">
    <div class="card-img-top">
        <div 
            id="map-{{ $status->id }}"
            class="map statusMap @if (\Request::is('status/*'))  large  @endif"
            data-polygon="{{ $status->trainCheckin->getMapLines() }}"
            data-showmapcontrols="@if (\Request::is('status/*')) 1 @endif"></div>
    </div>
    <div class="card-body">
        <ul class="timeline">
            <li>
                <span class="text-trwl">{{ $status->trainCheckin->getOrigin->name }} </span>
                <span class="text-trwl float-right">{{ date('H:i', strtotime($status->trainCheckin->departure)) }} Uhr</span>
                <p class="train-status"><i class="fas fa-subway"></i> {{ $status->trainCheckin->getHafasTrip->linename }}</p>
                @if(!empty($status->body))
                    <p class="status-body"><i class="fas fa-quote-right"></i> {{ $status->body }}</p>
                @endif
            </li>
            <li>
                <span class="text-trwl">{{ $status->trainCheckin->getDestination->name }}</span>
                <span class="text-trwl float-right">{{ date('H:i', strtotime($status->trainCheckin->arrival)) }} Uhr</span>
            </li>
        </ul>
    </div>
    <div class="card-footer text-muted interaction">
        <span class="float-right"><a href="{{ route('account.show', ['username' => $status->user->username]) }}">{{ $status->user->username }}</a> on <a href="{{ url('/status/'.$status->id) }}">{{ $status->created_at }}</a></span>
        <a href="#" class="like">{{ $status->likes->where('user_id', Auth::user()->id)->first() === null ? 'Like' : 'Dislike'}}</a>
        @if(Auth::user() == $status->user)
            |
            <a href="#" class="edit">Edit</a> |
            <a href="#" class="delete">Delete</a>
        @endif
    </div>
</div>
