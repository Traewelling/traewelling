@auth
    @include('includes.follow-button')
    @if(auth()->user()->id != $user->id)
        <x-mute-button :user="$user"/>
        <x-block-button :user="$user"/>
    @endif
    @if(auth()->user()->hasRole('admin'))
        <a href="{{ route('admin.users.show', ['id' => $user->id]) }}"
           class="btn btn-sm btn-outline-light">
            <i class="fa fa-tools"></i>
        </a>
    @endif
@endauth
