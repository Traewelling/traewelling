@php use App\Enum\EventRejectionReason; @endphp
<div class="btn-group" role="group">
    <button type="button" class="btn btn-primary dropdown-toggle"
            data-bs-toggle="dropdown" aria-expanded="false">
        Decline
    </button>
    <ul class="dropdown-menu">
        <li>
            <button class="btn-link dropdown-item" name="declineReason"
                    value="{{EventRejectionReason::LATE}}">Too late
            </button>
        </li>
        <li>
            <button class="btn-link dropdown-item" name="declineReason"
                    value="{{EventRejectionReason::DUPLICATE}}">Duplicate
            </button>
        </li>
        <li>
            <button class="btn-link dropdown-item" name="declineReason"
                    value="{{EventRejectionReason::NOT_APPLICABLE}}">No Value
            </button>
        </li>
        <li>
            <button class="btn-link dropdown-item" name="declineReason"
                    value="{{EventRejectionReason::DEFAULT}}">No Reason
            </button>
        </li>
    </ul>
</div>
