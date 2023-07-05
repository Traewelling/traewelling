<div class="btn-group">
    <button class="btn btn-sm btn-outline-primary dropdown-toggle"
            type="button"
            id="visibilityDropdownButton"
            data-mdb-dropdown-animation="off"
            data-mdb-toggle="dropdown"
            aria-expanded="false"
    >
        <i class="fa fa-{{['globe-americas', 'lock-open', 'user-friends', 'lock', 'user-check'][auth()->user()?->default_status_visibility->value ?? 0]}}"
           aria-hidden="true"></i>
    </button>
    <ul class="dropdown-menu" aria-labelledby="visibilityDropdownButton">
        @foreach(\App\Enum\StatusVisibility::cases() as $visibility)
            <li class="dropdown-item trwl-visibility-item" data-trwl-visibility="{{$visibility->value}}">
                <i class="fa fa-{{['globe-americas', 'lock-open', 'user-friends', 'lock', 'user-check'][$visibility->value]}}"
                   aria-hidden="true"></i> {{ __('status.visibility.' . $visibility->value) }}
                <br/>
                <span class="text-muted"> {{ __('status.visibility.' . $visibility->value . '.detail') }}</span>
            </li>
        @endforeach
    </ul>
    <input type="hidden" id="checkinVisibility" name="checkinVisibility"
           value="{{auth()->user()?->default_status_visibility->value ?? 0}}"/>
</div>
