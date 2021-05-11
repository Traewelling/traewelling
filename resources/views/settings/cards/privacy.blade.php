<div class="card mt-3">
    <div class="card-header">{{ __('settings.title-privacy') }}</div>

    <div class="card-body">
        <form method="POST" action="{{ route('settings.privacy') }}">
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
            <hr/>
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
                    <a href="{{route('settings.follower')}}">
                        {{__('settings.follower.manage')}}
                    </a>
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
