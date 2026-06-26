@extends('layouts.minimal-tailwind')

@section('title', __('menu.oauth_authorize.title'))
@section('meta-robots', 'noindex')

@section('content')
    <div class="bg-base-200 flex-col min-h-screen items-center w-full">
        <div class="card mx-auto w-full max-w-5xl shadow-xl mt-0 md:pt-12 pb-5">
            @if($client->user_id !== 0)
                <div class="alert alert-error mb-4">
                    <i class="fas fa-warning flex-shrink-0 me-2 bi"></i>
                    {{ __("menu.oauth_authorize.third_party") }}
                    <a href="https://help.traewelling.de/safety-and-security/apps/#drittanbieter-apps"
                       class="btn btn-sm"
                       target="_blank">
                        {{ __("menu.oauth_authorize.third_party.more") }}
                    </a>
                </div>
            @endif
            <div class="bg-base-100 md:rounded-xl mb-4">
                <div class="px-10 py-12">
                    <h1 class="text-2xl font-bold self-center">
                        {{__("menu.oauth_authorize.request_title")}}
                    </h1>
                    <div class="flex flex-col gap-4">
                        <!-- Introduction -->
                        <p class="my-4 italic @if($client->user_id !== 0) text-warning @endif">{!!__("menu.oauth_authorize.request", ['application' => e($client->name)])!!}</p>

                        <!-- Scope List -->
                        @if (count($scopes) > 0)
                            <div class="prose">
                                <p><strong>{{ __("menu.oauth_authorize.scopes_title") }}</strong></p>

                                <ul>
                                    @foreach ($scopes as $scope)
                                        <li @class(['text-error' => str_starts_with($scope->id, 'extra')])>
                                            {{ __("scopes.".$scope->id) }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <!-- Webhook -->
                        @if ($webhook)
                            <div class="prose">
                                <p>{{ __("menu.oauth_authorize.webhook_request") }}</p>

                                <ul>
                                    @foreach ($webhook['events'] as $event)
                                        <li>{{ __("settings.webhook_event." . $event) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($client->user_id !== 0)
                <div class="alert alert-error mb-4">
                    <i class="fas fa-warning flex-shrink-0 me-2 bi"></i>
                    {{ __("menu.oauth_authorize.third_party") }}
                    <a href="https://help.traewelling.de/safety-and-security/apps/#drittanbieter-apps"
                       class="btn btn-sm"
                       target="_blank">
                        {{ __("menu.oauth_authorize.third_party.more") }}
                    </a>
                </div>
            @endif
            @if ($client->privacy_policy_url)
                <div class="bg-base-100 md:rounded-xl mb-4 flex justify-around gap-4 px-0 py-4 text-sm">
                    <p class="text-center">
                        <a class="link" href="{{ $client->privacy_policy_url }}">
                            {{ __("menu.oauth_authorize.application_information.privacy_policy", [
                                "client" => $client->name,
                                ]) }}
                        </a>
                    </p>
                </div>
            @endif

            @if($client->user_id !== 0)
                <div class="bg-base-100 md:rounded-xl mb-4 flex justify-around gap-4 px-0 py-4 text-sm">
                    <div class="stat px-1">
                        <p class="text-center">
                            {!! __("menu.oauth_authorize.application_information.author", [
                            "application" => e($client->name),
                            "user" => e($author),
                            "url" => route("profile", $author)
                            ])!!}
                        </p>
                    </div>

                    <div class="stat px-1">
                        <p class="text-center">
                            {{ __("menu.oauth_authorize.application_information.created_at", [
                            "time" => $client->created_at->diffForHumans()
                        ]) }}
                        </p>
                    </div>

                    <div class="stat px-1">
                        <p class="text-center">
                            {{ trans_choice(
                                "menu.oauth_authorize.application_information.user_count",
                                $userCount
                               )
                            }}
                        </p>
                    </div>
                </div>
            @endif

            <div class="bg-base-100 md:rounded-xl mb-4 flex justify-around gap-4 px-10 py-4">
                <!-- Authorize Button -->
                <form method="post" action="{{ route('oauth.authorizations.approve') }}">
                    @csrf

                    <input type="hidden" name="state" value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button type="submit" class="btn btn-success btn-approve">
                        {{__("menu.oauth_authorize.authorize")}}
                    </button>
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
    </div>
@endsection
