@if(auth()->user()->id != $user->id)
    <x-mute-button :user="$user"/>
    @if($user->isBlockedByAuthUser)
        <form style="display: inline;" method="POST" action="{{route('user.unblock')}}">
            @csrf
            <input type="hidden" name="user_id" value="{{$user->id}}"/>
            <button type="submit" class="btn btn-sm btn-primary" data-mdb-toggle="tooltip"
                    title="{{ __('user.unblock-tooltip') }}">
                <i class="fa-solid fa-ban"></i> <i class="fa-solid fa-unlock"></i>
            </button>
        </form>
    @else
        <form style="display: inline;" method="POST" action="{{route('user.block')}}">
            @csrf
            <input type="hidden" name="user_id" value="{{$user->id}}"/>
            <button type="submit" class="btn btn-sm btn-primary" data-mdb-toggle="tooltip"
                    title="{{ __('user.block-tooltip') }}">
                <i class="fa-solid fa-ban"></i>
            </button>
        </form>
    @endif
@endif
