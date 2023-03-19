@extends('layouts.settings')

@section('title', request()->is('dev.apps.create') ? 'Anwendung erstellen' : 'Anwendung bearbeiten') <!-- ToDo: Übersetzen -->

@section('content')
    <div class="row">
        <div class="col-12">
            <form enctype="multipart/form-data" method="POST"
                  action="{{Route::currentRouteName() === 'dev.apps.create' ? route('dev.apps.create.post') : route('dev.apps.edit', ['appId' => $app->id]) }}"
            >
                @csrf

                @isset($app->id)
                    <div class="row my-2">
                        <table class="table table-striped table-dark">
                            <tr>
                                <td>Client Id</td>
                                <td><code>{{ $app->id }}</code></td>
                            </tr>
                            @if($app->confidential())
                                <tr>
                                    <td>Client Secret</td>
                                    <td><code>{{ $app->secret }}</code></td>
                                </tr>
                            @endif
                        </table>
                        <hr>
                    </div>
                @endisset
                <div class="form-group row my-1">
                    <label for="name" class="col-md-4 col-form-label text-md-right">
                        Name
                    </label>

                    <div class="col-md-6">
                        <input id="name" type="text" class="form-control" name="name" placeholder="My Application"
                               required
                               value="{{ $app?->name }}" autofocus>
                    </div>
                </div>

                <div class="form-group row my-1">
                    <label for="redirect" class="col-md-4 col-form-label text-md-right">
                        Redirect URL
                    </label>
                    <div class="col-md-6">
                        <input id="redirect" type="text" class="form-control" name="redirect"
                               placeholder="https://example.com/callback" required
                               value="{{ $app?->redirect }}">
                    </div>
                </div>

                <div class="form-group row my-1">
                    <label for="confidential" class="col-md-4 col-form-label text-md-right">
                        Confidential
                    </label>
                    <div class="col-md-6">
                        <input id="confidential" type="checkbox" name="confidential"
                               @if(!$app || $app->confidential())
                                   checked
                               @endif
                               @if(Route::currentRouteName() === 'dev.apps.edit')
                                   disabled
                            @endif
                        >
                    </div>


                </div>
                <div class="form-group row my-1">
                    <label for="enable_webhooks" class="col-md-4 col-form-label text-md-right">
                        Enable Webhooks (Experimental)
                    </label>
                    <div class="col-md-6">
                        <input id="enable_webhooks" type="checkbox" name="enable_webhooks"
                            @if($app?->webhooks_enabled)
                            checked
                            @endif
                        >
                    </div>
                </div>
                <div class="form-group row my-1">
                    <label for="authorized_webhook_url" class="col-md-4 col-form-label text-md-right">
                       Authorized Webhook URL
                    </label>
                    <div class="col-md-6">
                        <input id="authorized_webhook_url" type="text" class="form-control" name="authorized_webhook_url"
                               placeholder="https://example.com/webhook"
                               value="{{ $app?->authorized_webhook_url }}">
                    </div>
                </div>
                <div class="form-group row my-1">
                    <label for="privacy_policy_url" class="col-md-4 col-form-label text-md-right">
                       Privacy Policy
                    </label>
                    <div class="col-md-6">
                        <input id="privacy_policy_url" type="text" class="form-control" name="privacy_policy_url"
                               placeholder="https://example.com/privacy"
                               value="{{ $app?->privacy_policy_url }}">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="alert alert-warning">
                        @if(Route::currentRouteName() === "dev.apps.create")

                            <p>Make sure to check if you need a confidential client or a public client. This setting
                                cannot be changed later, because of a technical limitation.</p>
                            <p>This setting depends on which <a href="https://oauth.net/2/grant-types/">OAuth Grant
                                    Type</a> you want to use.</p>
                            <p>For a regular <a href="https://oauth.net/2/grant-types/authorization-code/">Authorization
                                    Code Flow</a>, Confidential needs to be set to true. Use this if you can keep
                                the
                                secret secure. (e.g. Server Side Apps)</p>
                            <p>If you want to use a <a href="https://oauth.net/2/pkce/">Authorization Code with
                                    Proof
                                    Key for Code Exchange Flow</a>, Confidential needs to be set to false. Use this
                                if
                                you can't keep the secret secure. (e.g. SPAs, Mobile Apps)</p>
                            <p>TLDR; Check Confidential if you need a Client Secret.</p>

                        @else
                            <p>
                                Note:
                                You don't have a client secret because your using a non-confidential client using
                                the
                                <a href="https://oauth.net/2/pkce/">
                                    Authorization Code flow with Proof Key for Code Exchange
                                </a>
                            </p>
                        @endif
                    </div>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            @if(Route::currentRouteName() === 'dev.apps.create')
                                Erstellen
                            @else
                                Aktualisieren
                            @endif
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
