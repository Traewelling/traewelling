<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    @if ($errors->any())
                    @foreach ($errors->all() as $error)
                    window.notyf.error('{!! $error !!}');
                    @endforeach
                    @endif

                    @foreach(['success', 'error', 'warning', 'info'] as $type)
                    @if ($message = session()->get($type))
                    window.notyf.open({
                        type: '{{ $type}}',
                        message: '{{ $message }}'
                    });
                    @endif
                    @endforeach

                    @if(session()->has('message'))
                    window.notyf.open({
                        'type': 'error',
                        'message': '{!! session()->get('message') !!}'
                    })
                    @endif
                });
            </script>

            @if(!request()->routeIs('gdpr.intercept'))
                @include('includes.messages.mail-verification')
            @endif
            @if(session()->has('checkin-success'))
                @php $success = session()->get('checkin-success') @endphp
                <x-checkin-success
                        :id="$success->id"
                        :distance="$success->distance"
                        :duration="$success->duration"
                        :points="$success->points"
                        :pointReason="$success->pointReason->value"
                        :lineName="$success->lineName"
                        :socialText="$success->socialText"
                        :alsoOnThisConnection="$success->alsoOnThisConnection"
                        :event="$success->event"
                        :forced="$success->forced"
                        :reason="$success->reason"
                />
            @endif
            <div id="alert_placeholder"></div>
        </div>
    </div>
</div>
