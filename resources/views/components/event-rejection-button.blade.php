@php use App\Enum\EventRejectionReason; @endphp
<div class="btn-group" role="group">
    <button type="button" class="btn btn-primary dropdown-toggle"
            data-bs-toggle="dropdown" aria-expanded="false">
        Decline
    </button>
    <ul class="dropdown-menu">
        <li>
            <button class="btn-link dropdown-item" name="rejectionReason"
                    value="{{EventRejectionReason::LATE}}">Too late
            </button>
        </li>
        <li>
            <button class="btn-link dropdown-item" name="rejectionReason"
                    value="{{EventRejectionReason::DUPLICATE}}">Duplicate
            </button>
        </li>
        <li>
            <button class="btn-link dropdown-item" name="rejectionReason"
                    value="{{EventRejectionReason::NOT_APPLICABLE}}">No Value
            </button>
        </li>
        <li>
            <button class="btn-link dropdown-item" name="rejectionReason"
                    value="{{EventRejectionReason::MISSING_INFORMATION}}">Missing information
            </button>
        </li>
        <li>
            <button class="btn-link dropdown-item" name="rejectionReason"
                    value="{{EventRejectionReason::DEFAULT}}">No Reason
            </button>
        </li>
    </ul>
</div>
