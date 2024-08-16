@extends('layouts.settings')
@section('title', __('settings.title-privacy'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card mb-3">
                <div class="card-header">{{ __('settings.title-privacy') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('settings.privacy.update') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">
                                {{ __('settings.hide-search-engines') }}
                                <i class="fas fa-info-circle" title="{{__('settings.search-engines.description')}}"
                                   data-mdb-toggle="tooltip"></i>
                            </label>
                            <div class="col-md-6">
                                <select class="form-control" name="prevent_index">
                                    <option value="0" @if(auth()->user()->prevent_index == 0) selected @endif>
                                        {{__('settings.allow')}}
                                    </option>
                                    <option value="1" @if(auth()->user()->prevent_index == 1) selected @endif>
                                        {{__('settings.prevent')}}
                                    </option>
                                </select>

                                @error('prevent_index')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">
                                {{ __('settings.visibility.default') }}
                            </label>
                            <div class="col-md-6">
                                <select class="form-control" name="default_status_visibility">
                                    @foreach(\App\Enum\StatusVisibility::cases() as $visibility)
                                        <option value="{{$visibility->value}}"
                                                @if(auth()->user()->default_status_visibility === $visibility) selected @endif>
                                            {{__('status.visibility.' . $visibility->value)}}
                                        </option>
                                    @endforeach
                                </select>

                                @error('prevent_index')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">
                                {{ __('settings.visibility.hide') }}
                                <i class="fas fa-info-circle" title="{{__('settings.visibility.hide.explain')}}"
                                   data-mdb-toggle="tooltip"></i>
                            </label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input class="form-control" type="number" name="privacy_hide_days" min="1"
                                           value="{{auth()->user()->privacy_hide_days}}"/>
                                    <span class="input-group-text">{{__('time.days')}}</span>
                                </div>
                                <small>{{__('empty-input-disable-function')}}</small>

                                @error('privacy_hide_days')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">
                                {{ __('settings.mastodon.visibility') }}
                            </label>
                            <div class="col-md-6">
                                <select class="form-control" name="mastodon_visibility">
                                    @foreach(\App\Enum\MastodonVisibility::cases() as $visibility)
                                        <option value="{{$visibility->value}}"
                                                @if(auth()->user()->socialProfile->mastodon_visibility === $visibility) selected @endif>
                                            {{__('settings.mastodon.visibility.' . $visibility->value)}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr/>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    <input id="likes_enabled" type="checkbox"
                                           class="custom-control-input @error('likes_enabled') is-invalid @enderror"
                                           name="likes_enabled" {{ auth()->user()->likes_enabled ? 'checked' : '' }} />
                                    <label class="custom-control-label" for="likes_enabled">
                                        {{ __('user.likes-enabled') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    <input id="points_enabled" type="checkbox"
                                           class="custom-control-input @error('points_enabled') is-invalid @enderror"
                                           name="points_enabled" {{ auth()->user()->points_enabled ? 'checked' : '' }} />
                                    {{ __('user.points-enabled') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    <input id="private_profile" type="checkbox"
                                           class="custom-control-input @error('private_profile') is-invalid @enderror"
                                           name="private_profile" {{ auth()->user()->private_profile ? 'checked' : '' }} />
                                    <label class="custom-control-label" for="private_profile">
                                        {{ __('user.private-profile') }}
                                    </label>
                                </div>
                                @error('private_profile')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <hr/>
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('settings.btn-update') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            @if(auth()->user()?->hasRole('open-beta'))
                <div id="settings-friend-checkin">
                    <friend-checkin-settings>
                    </friend-checkin-settings>
                </div>
            @endif
        </div>
    </div>
@endsection
