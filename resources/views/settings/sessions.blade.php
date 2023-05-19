@extends('layouts.settings')

@section('content')
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
                <tbody>
                    @foreach($sessions as $session)
                        <tr>
                            <td><i class="fas fa-{{ $session->device_icon }}"></i></td>
                            <td>{{ $session->platform }}</td>
                            <td>{{ $session->ip_address }}</td>
                            <td>{{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <form method="POST" action="{{ route('delsession') }}">
                @csrf
                <button type="submit" class="btn btn-block btn-outline-danger mx-0">
                    {{ __('settings.deleteallsessions') }}
                </button>
            </form>
        </div>
    </div>
@endsection
