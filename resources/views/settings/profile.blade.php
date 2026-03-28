@extends(!auth()->user()?->hasRole('open-beta') ? 'layouts.settings' : 'layouts.tailwind-vue-layout')
@section('title', __('settings.title-profile'))

@section('content')
    <div class="row" id="settings-profile">
        <profile-settings></profile-settings>
        <div class="col-md-5">
            <div class="card mb-3">
                <div class="card-header">
                    {{ __('settings.picture') }}
                </div>
                <div class="text-center pb-3 pt-3">
                    <div class="image-box pb-2">
                        <img
                            src="{{ resolve(\App\Services\ProfilePictureService::class)->getUrl(auth()->user()) }}"
                            style="max-width: 96px" alt="{{__('settings.picture')}}"
                            id="theProfilePicture" loading="lazy" decoding="async"
                        />
                    </div>

                    <a href="#" class="btn btn-primary mb-1" data-bs-toggle="modal"
                       data-bs-target="#uploadAvatarModal">
                        {{__('settings.upload-image')}}
                    </a>
                    <br/>
                    <a href="javascript:void(0)"
                       class="btn btn-outline-danger btn-sm mb-3 {{isset(auth()->user()->avatar) ? '' : 'd-none'}}"
                       id="btnModalDeleteProfilePicture"
                       data-bs-toggle="modal"
                       data-bs-target="#deleteProfilePictureModal"
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="formFile" class="form-label">{{__('settings.choose-file')}}</label>
                        <input class="form-control" type="file" id="image" accept="image/*">
                    </div>

                    <div id="upload-demo" class="d-none"></div>
                    <div class="d-grid">
                        <button class="btn btn-primary upload-image d-none" id="upload-button"
                                data-bs-dismiss="modal"
                        >
                            {{__('settings.upload-image')}}
                        </button>
                    </div>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{!! __('settings.delete-profile-picture-desc') !!}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-danger" data-bs-dismiss="modal"
                            aria-label="{{ __('settings.delete-profile-picture-no') }}">
                        {{ __('settings.delete-profile-picture-no') }}
                    </button>
                    <button class="btn btn-danger"
                            onclick="Settings.deleteProfilePicture()"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteProfilePictureModal"
                    >
                        {{ __('settings.delete-profile-picture-yes') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
