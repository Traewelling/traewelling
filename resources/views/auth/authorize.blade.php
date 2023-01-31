@extends('layouts.app')

@section('title', __('menu.oauth_authorize_title'))
@section('meta-robots', 'noindex')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-default">
                <div class="card-header">
                    {{__("menu.oauth_authorize_request_title")}}
                </div>
                <div class="card-body">
                    <!-- Introduction -->
                    <p>{!!__("menu.oauth_authorize_request", ['application' => $client->name])!!}</p>

                    <!-- TODO: Make this prettier once scopes are a thing -->
                    <!-- Scope List -->
                    @if (count($scopes) > 0)
                    <div class="scopes">
                        <p><strong>This application will be able to:</strong></p>

                        <ul>
                            @foreach ($scopes as $scope)
                            <li>{{ $scope->description }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Webhook -->
                    @if ($webhook)
                    <div>
                        <p>{{ __("menu.oauth_authorize_webhook_request") }}</p>

                        <ul>
                            @foreach ($webhook['events'] as $event)
                            <li>{{ __("settings.webhook_event." . $event) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="d-flex justify-content-evenly">
                        <!-- Authorize Button -->
                        <form method="post" action="{{ route('oauth.authorizations.approve') }}">
                            @csrf

                            <input type="hidden" name="state" value="{{ $request->state }}">
                            <input type="hidden" name="client_id" value="{{ $client->id }}">
                            <input type="hidden" name="auth_token" value="{{ $authToken }}">
                            <button type="submit" class="btn btn-success btn-approve">{{__("menu.oauth_authorize_authorize")}}</button>
                        </form>

                        <!-- Cancel Button -->
                        <form method="post" action="{{ route('passport.authorizations.deny') }}">
                            @csrf
                            @method('DELETE')

                            <input type="hidden" name="state" value="{{ $request->state }}">
                            <input type="hidden" name="client_id" value="{{ $client->id }}">
                            <input type="hidden" name="auth_token" value="{{ $authToken }}">
                            <button class="btn btn-danger">{{__("menu.oauth_authorize_cancel")}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
