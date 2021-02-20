<div class="card mt-3">
    <div class="card-header">{{ __('settings.title-sessions') }}</div>

    <div class="card-body">
        <table class="table table-responsive">
            <thead>
                <tr>
                    <th>{{ __('settings.device') }}</th>
                    <th>{{ __('settings.platform') }}</th>
                    <th>{{ __('settings.ip') }}</th>
                    <th>{{ __('settings.lastactivity') }}</th>
                </tr>
            </thead>
            @foreach($sessions as $session)
                <tr>
                    <td><i class="fas fa-{{ $session['device'] }}"></i></td>
                    <td>{{ $session['platform'] }}</td>
                    <td>{{ $session['ip'] }}</td>
                    <td>{{ date('Y-m-d H:i:s', $session['last']) }}</td>
                </tr>
            @endforeach

        </table>
        <form method="POST" action="{{ route('delsession') }}">
            @csrf
            <button type="submit" class="btn btn-block btn-outline-danger mx-0">
                {{ __('settings.deleteallsessions') }}
            </button>
        </form>

    </div>
</div>