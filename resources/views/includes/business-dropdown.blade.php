<div class="btn-group">
    <button class="btn btn-sm btn-outline-twitter dropdown-toggle"
            type="button"
            id="businessDropdownButton"
            data-mdb-dropdown-animation="off"
            data-mdb-toggle="dropdown"
            aria-expanded="false"
    >
        <i class="fa fa-user"></i>
    </button>
    <ul id="businessDropdown" class="dropdown-menu" aria-labelledby="businessDropdownButton">
        <li class="dropdown-item trwl-business-item" data-trwl-business="0">
            <i class="fa fa-user"></i> {{ __('stationboard.business.private') }}
        </li>
        <li class="dropdown-item trwl-business-item" data-trwl-business="1">
            <i class="fa fa-briefcase"></i> {{ __('stationboard.business.business') }}
            <br/>
            <span class="text-muted"> {{ __('stationboard.business.business.detail') }}</span>
        </li>
        <li class="dropdown-item trwl-business-item" data-trwl-business="2">
            <i class="fa fa-building"></i> {{ __('stationboard.business.commute') }}
            <br/>
            <span class="text-muted"> {{ __('stationboard.business.commute.detail') }}</span>
        </li>
    </ul>
    <input type="hidden" id="business_check" name="business_check" value="0"/>
</div>
