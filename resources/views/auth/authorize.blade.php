@extends('layouts.app')

@section('title', __('menu.oauth_authorize.title'))
@section('meta-robots', 'noindex')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-header">
                    {{__("menu.oauth_authorize.request_title")}}
                </div>
                <div class="card-body">
                    <!-- Introduction -->
                    <p>{!!__("menu.oauth_authorize.request", ['application' => $client->name])!!}</p>

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
                        <p>{{ __("menu.oauth_authorize.webhook_request") }}</p>

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
                            <button type="submit" class="btn btn-success btn-approve">{{__("menu.oauth_authorize.authorize")}}</button>
                        </form>

                        <!-- Cancel Button -->
                        <form method="post" action="{{ route('passport.authorizations.deny') }}">
                            @csrf
                            @method('DELETE')

                            <input type="hidden" name="state" value="{{ $request->state }}">
                            <input type="hidden" name="client_id" value="{{ $client->id }}">
                            <input type="hidden" name="auth_token" value="{{ $authToken }}">
                            <button class="btn btn-danger">{{__("menu.oauth_authorize.cancel")}}</button>
                        </form>
                    </div>

                </div>
                <div class="card-footer row">
                    <p class="m-0 col-md-4 text-center">{!! __("menu.oauth_authorize.application_information.author", [
                        "application" => $client->name,
                        "user" => $author,
                        "url" => route("profile", $author)
                        ])!!}</p>
                    <p class="m-0 col-md-4 text-center">{{ __("menu.oauth_authorize.application_information.created_at", [
                        "time" => $client->created_at->diffForHumans()
                    ]); }}</p>
                    <p class="m-0 col-md-4 text-center">
                        {{ trans_choice(
                            "menu.oauth_authorize.application_information.user_count",
                            $userCount
                           );
                        }}
                    </p>
                    @if ($client->privacy_policy_url)
                    <p class="m-0 col-md-12 text-center">
                        <a href="{{ $client->privacy_policy_url }}">{{ __("menu.oauth_authorize.application_information.privacy_policy", [
                            "client" => $client->name,
                        ]) }}</a>
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
