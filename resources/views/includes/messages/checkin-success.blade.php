@if(session()->has('checkin-success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __('controller.transport.checkin-heading') }}</h4>
        <p>{{ trans_choice('controller.transport.checkin-ok', preg_match('/\s/', session()->get('checkin-success')['lineName']), ['lineName' => session()->get('checkin-success')['lineName']]) }}</p>

        @if(session()->get('checkin-success')['event'])
            <p>
                {!!  __('events.on-your-way', [
                    "name" => session()->get('checkin-success')['event']['name'],
                    "url" => route('statuses.byEvent', ['eventSlug' => session()->get('checkin-success')['event']['slug']])
                ]) !!}
            </p>
        @endif

    </div>
@endif
