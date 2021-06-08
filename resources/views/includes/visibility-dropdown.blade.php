<div class="btn-group">
    <button class="btn btn-sm btn-outline-twitter dropdown-toggle"
            type="button"
            id="visibilityDropdownButton"
            data-mdb-toggle="dropdown"
            aria-expanded="false"
    >
        <i class="fa fa-globe-americas" aria-hidden="true"></i>
    </button>
    <ul class="dropdown-menu" aria-labelledby="visibilityDropdownButton">
        <li class="dropdown-item trwl-visibility-item" data-trwl-visibility="0">
            <i class="fa fa-globe-americas" aria-hidden="true"></i> {{ __('status.visibility.0') }}
            <br/>
            <span class="text-muted"> {{ __('status.visibility.0.detail') }}</span>
        </li>
        <li class="dropdown-item trwl-visibility-item" data-trwl-visibility="1">
            <i class="fa fa-lock-open" aria-hidden="true"></i> {{ __('status.visibility.1') }}
            <br/>
            <span class="text-muted"> {{ __('status.visibility.1.detail') }}</span>
        </li>
        <li class="dropdown-item trwl-visibility-item" data-trwl-visibility="2">
            <i class="fa fa-user-friends" aria-hidden="true"></i> {{ __('status.visibility.2') }}
            <br/>
            <span class="text-muted"> {{ __('status.visibility.2.detail') }}</span>
        </li>
        <li class="dropdown-item trwl-visibility-item" data-trwl-visibility="3">
            <i class="fa fa-lock" aria-hidden="true"></i> {{ __('status.visibility.3') }}
            <br/>
            <span class="text-muted"> {{ __('status.visibility.3.detail') }}</span>
        </li>
    </ul>
    <input type="hidden" id="checkinVisibility" name="checkinVisibility" value="0"/>
</div>
