<div class="card status mt-3" data-statusid="{{ $status->id }}">
    <div class="card-body">
        <ul class="timeline">
            <li>
                <span class="text-trwl">{{ $status->trainCheckin->getOrigin->name }} </span>
                <span class="text-trwl float-right">{{ date('H:i', strtotime($status->trainCheckin->departure)) }} Uhr</span>
                <p class="train-status">{{ $status->trainCheckin->getHafasTrip->linename }}</p>
                @if(!empty($status->body))
                    <p class="status-body">{{ $status->body }}</p>
                @endif
            </li>
            <li>
                <span class="text-trwl">{{ $status->trainCheckin->getDestination->name }}</span>
                <span class="text-trwl float-right">{{ date('H:i', strtotime($status->trainCheckin->arrival)) }} Uhr</span>
            </li>
        </ul>
    </div>
    <div class="card-footer text-muted interaction">
        <span class="float-right"><a href="{{ route('account.show', ['username' => $status->user->username]) }}">{{ $status->user->username }}</a> on {{ $status->created_at }}</span>
        <a href="#" class="like">{{ $status->likes->where('user_id', Auth::user()->id)->first() === null ? 'Like' : 'Dislike'}}</a>
        @if(Auth::user() == $status->user)
            |
            <a href="#" class="edit">Edit</a> |
            <a href="#" class="delete">Delete</a>
        @endif
    </div>
</div>
