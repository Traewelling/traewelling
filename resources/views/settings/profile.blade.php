@extends('layouts.settings')
@section('title', __('settings.title-profile'))

@section('content')
    <div class="row">
        <div class="col-md-7">
            <div class="card mb-3">
                <div class="card-header">{{ __('settings.title-profile') }}</div>

                <div class="card-body">
                    <form enctype="multipart/form-data" method="POST" action="{{ route('settings.profile') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">
                                {{ __('user.username') }}
                            </label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input id="username" type="text"
                                           class="form-control @error('username') is-invalid @enderror"
                                           name="username"
                                           value="{{ auth()->user()->username }}"
                                           required
                                    />
                                </div>

                                @error('username')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">
                                {{ __('user.displayname') }}
                            </label>
                            <div class="col-md-6">
                                <input id="name" type="text"
                                       class="form-control @error('name') is-invalid @enderror" name="name"
                                       value="{{ auth()->user()->name }}" required autocomplete="name"/>

                                @error('name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">
                                {{ __('user.email') }}
                            </label>
                            <div class="col-md-6">
                                <input id="email" type="email"
                                       class="form-control @error('email') is-invalid @enderror" name="email"
                                       value="{{ auth()->user()->email }}" autocomplete="email"/>

                                @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="mapprovider" class="col-md-4 col-form-label text-md-right">
                                {{ __('user.mapprovider') }}
                            </label>
                            <div class="col-md-6">
                                <select class="form-control" name="mapprovider">
                                    <option value="{{ App\Enum\MapProvider::CARGO->value }}"
                                            @if(auth()->user()->mapprovider == App\Enum\MapProvider::CARGO) selected @endif>
                                        {{__('map-providers.cargo')}}
                                    </option>
                                    <option value="{{ App\Enum\MapProvider::OPEN_RAILWAY_MAP->value }}"
                                            @if(auth()->user()->mapprovider == App\Enum\MapProvider::OPEN_RAILWAY_MAP) selected @endif>
                                        {{__('map-providers.open-railway-map')}}
                                    </option>
                                </select>

                                @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="timezone" class="col-md-4 col-form-label text-md-right">
                                {{ __('user.timezone') }}
                            </label>
                            <div class="col-md-6">
                                <input
                                    class="form-control"
                                    list="datalistOptions"
                                    id="timezone"
                                    name="timezone"
                                    value="{{auth()->user()->timezone}}"
                                >
                                <datalist id="datalistOptions">
                                    @foreach(DateTimeZone::listIdentifiers() as $timezone)
                                        <option value="{{$timezone}}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="experimental" class="col-md-4 col-form-label text-md-right">
                                {{ __('settings.experimental') }}
                                <i class="fas fa-info-circle" title="{{__('settings.experimental.description')}}"
                                   data-mdb-toggle="tooltip"></i>
                            </label>
                            <div class="col-md-6">
                                <select class="form-control" name="experimental" id="experimental">
                                    <option value="1" @if(auth()->user()->hasRole('open-beta')) selected @endif>
                                        {{__('settings.allow')}}
                                    </option>
                                    <option value="0" @if(!auth()->user()->hasRole('open-beta')) selected @endif>
                                        {{__('settings.prevent')}}
                                    </option>
                                </select>

                                @error('prevent_index')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>


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
            <div class="card mb-3">
                <div class="card-header">
                    {{ __('settings.picture') }}
                </div>
                <div class="text-center pb-3 pt-3">
                    <div class="image-box pb-2">
                        <img
                            src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl(auth()->user()) }}"
                            style="max-width: 96px" alt="{{__('settings.picture')}}"
                            id="theProfilePicture" loading="lazy" decoding="async"
                        />
                    </div>

                    <a href="#" class="btn btn-primary mb-1" data-mdb-toggle="modal"
                       data-mdb-target="#uploadAvatarModal">
                        {{__('settings.upload-image')}}
                    </a>
                    <br/>
                    <a href="javascript:void(0)"
                       class="btn btn-outline-danger btn-sm mb-3 {{isset(auth()->user()->avatar) ? '' : 'd-none'}}"
                       id="btnModalDeleteProfilePicture"
                       data-mdb-toggle="modal"
                       data-mdb-target="#deleteProfilePictureModal"
                    >
                        {{ __('settings.delete-profile-picture-btn') }}
                    </a>

                    @error('avatar')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="uploadAvatarModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="mb-0">{{__('settings.upload-image')}}</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        <strong>{{__('settings.choose-file')}}: </strong>
                        <input type="file" id="image">
                    </p>

                    <div id="upload-demo" class="d-none"></div>
                    <button class="btn btn-primary btn-block upload-image d-none" id="upload-button"
                            data-mdb-dismiss="modal"
                    >
                        {{__('settings.upload-image')}}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteProfilePictureModal" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="mb-0">{{__('settings.delete-profile-picture')}}:</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{!! __('settings.delete-profile-picture-desc') !!}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-danger" data-mdb-dismiss="modal"
                            aria-label="{{ __('settings.delete-profile-picture-no') }}">
                        {{ __('settings.delete-profile-picture-no') }}
                    </button>
                    <a href="#" class="btn btn-danger"
                       onclick="Settings.deleteProfilePicture()"
                       data-mdb-toggle="modal"
                       data-mdb-target="#deleteProfilePictureModal"
                    >
                        {{ __('settings.delete-profile-picture-yes') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
