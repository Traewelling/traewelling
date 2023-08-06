@php use App\Repositories\OAuthClientRepository; @endphp
@extends('layouts.settings')

@section('title', isset($app) ? __('edit-app') : __('create-app'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card mb-3">
                <div class="card-header">{{__(isset($app) ? __('edit-app') : __('create-app'))}}</div>
                <div class="card-body">
                    <form enctype="multipart/form-data" method="POST"
                          action="{{Route::currentRouteName() === 'dev.apps.create' ? route('dev.apps.create.post') : route('dev.apps.edit', ['appId' => $app->id]) }}"
                    >
                        @csrf

                        @isset($app)
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
                            <div class="alert alert-warning">
                                @if($app?->confidential())
                                    WARNING: Changing the <code>confidential</code> field will delete your client secret
                                    and revoke all existing tokens.
                                @else
                                    WARNING: Changing the <code>confidential</code> field will generate a new client
                                    secret and revoke all existing tokens.
                                @endif
                            </div>
                        @endisset
                        <div class="form-group row my-1">
                            <label for="name" class="col-md-4 col-form-label text-md-right">
                                Name
                            </label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name"
                                       placeholder="My Application"
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
                                       @if((new OAuthClientRepository)->hasWebhooks($app->id))
                                           disabled
                                    @endif
                                    @endif
                                >
                            </div>
                        </div>
                        <div class="form-group row my-1">
                            <label for="authorized_webhook_url" class="col-md-4 col-form-label text-md-right">
                                Authorized Webhook URL
                            </label>
                            <div class="col-md-6">
                                <input id="authorized_webhook_url" type="text" class="form-control"
                                       name="authorized_webhook_url"
                                       placeholder="https://example.com/webhook"
                                       value="{{ $app?->authorized_webhook_url }}">
                            </div>
                        </div>
                        <div class="form-group row my-1">
                            <label for="privacy_policy_url" class="col-md-4 col-form-label text-md-right">
                                Privacy Policy
                            </label>
                            <div class="col-md-6">
                                <input id="privacy_policy_url" type="text" class="form-control"
                                       name="privacy_policy_url"
                                       placeholder="https://example.com/privacy"
                                       value="{{ $app?->privacy_policy_url }}">
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    @if(Route::currentRouteName() === 'dev.apps.create')
                                        {{__('create-app')}}
                                    @else
                                        {{__('refresh')}}
                                    @endif
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
