@if(auth()->user()->id != $user->id)
    @if(auth()->user()->mutedUsers->contains('id', $user->id))
        <form style="display: inline;" method="POST" action="{{route('user.unmute')}}">
            @csrf
            <input type="hidden" name="user_id" value="{{$user->id}}"/>
            <button type="submit" class="btn btn-sm btn-primary" data-mdb-toggle="tooltip"
                    title="{{ __('user.unmute-tooltip') }}">
                <i class="far fa-eye"></i>
            </button>
        </form>
    @else
        <form style="display: inline;" method="POST" action="{{route('user.mute')}}">
            @csrf
            <input type="hidden" name="user_id" value="{{$user->id}}"/>
            <button type="submit" class="btn btn-sm btn-primary" data-mdb-toggle="tooltip"
                    title="{{ __('user.mute-tooltip') }}">
                <i class="far fa-eye-slash"></i>
            </button>
        </form>
    @endif
@endif