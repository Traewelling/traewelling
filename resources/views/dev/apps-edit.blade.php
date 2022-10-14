@extends('layouts.settings')

@section('title', request()->is('dev.apps.create') ? 'Anwendung erstellen' : 'Anwendung bearbeiten') <!-- ToDo: Übersetzen -->

@section('content')
    <div class="row">
        <form enctype="multipart/form-data"
              @if(Route::currentRouteName() === 'dev.apps.create')
                  method="POST" action="{{ route('dev.apps.create') }}">
            @else
                method="POST" action="{{ route('dev.apps.edit', ['appId' => $appId ?? 0]) }}">
                <div class="row my-2">
                    <table class="table table-striped table-dark">
                        <tr>
                            <td>Client Id</td>
                            <td><code>{{ $app->id }}</code></td>
                        </tr>
                        <!--
                        <tr>
                            <td>Client Key</td>
                            <td><code></code></td>
                        </tr>
                        -->
                        <tr>
                            <td>Client Secret</td>
                            <td><code>{{ $app->secret }}</code></td>
                        </tr>
                        <!--
                        <tr>
                            <td>Your Access Token</td>
                            <td>
                                <code>asgpo8zoqihepg</code><br>
                                <a href="#"><i class="fas fa-refresh" aria-hidden="true"></i> Regenerate Access Token</a>
                            </td>
                        </tr>
                        -->
                    </table>
                    <hr>
                </div>
            @endif
            @csrf
            <div class="form-group row my-1">
                <label for="name" class="col-md-4 col-form-label text-md-right">
                    Name
                </label>

                <div class="col-md-6">
                    <input id="name" type="text" class="form-control" name="name" placeholder="My Application" required
                           value="{{ $app ? $app->name : '' }}" autofocus>
                </div>
            </div>

            <div class="form-group row my-1">
                <label for="redirect" class="col-md-4 col-form-label text-md-right">
                    Redirect URL
                </label>
                <div class="col-md-6">
                    <input id="redirect" type="text" class="form-control" name="redirect" placeholder="https://example.com/callback" required value="{{ $app ? $app->redirect : ''}}">
                </div>
            </div>

            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        Aktualisieren
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
