<div class="row {{ $color }} {{ $read ? '' : 'unread' }}">
    <a class="col-1 align-left lead" href="{{ $link }}">
        <i class="{{ $icon }}"></i>
    </a>
    <a class="col-8 align-middle" href="{{ $link }}">
        <p class="lead">
            {!! $lead !!}
        </p>
        {!! $notice !!}&nbsp;
    </a>
    <div class="col-3 text-right">
        <button type="button" class="interact" aria-label="{{ $read ? __('notifications.mark-as-unread') : __('notifications.mark-as-read') }}">
            <span aria-hidden="true"><i class="far {{ $read ? 'fas fa-envelope-open' : 'far fa-envelope' }}"></i></span>
        </button>
        <div class="text-muted">{{ $date_for_humans }}</div>
    </div>
</div>