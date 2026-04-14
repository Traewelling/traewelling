@extends(request()->routeIs('embed.*') ? 'layouts.app-embed' : (!auth()->user()?->hasRole('open-beta') ? 'layouts.app' : 'layouts.tailwind-vue-layout'))

@section('title', __('search-results'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <form class="form-inline" action="/search">
                    <div class="input-group md-form form-sm form-2 ps-0 m-0 mb-2">
                        <input
                            name="searchQuery"
                            type="text"
                            value="{{ request('searchQuery') }}"
                            class="border border-white rounded-left form-control my-0 py-1"
                            placeholder="{{__('stationboard.submit-search')}}"
                            aria-label="{{__('stationboard.submit-search')}}"
                            required
                        />
                        <button
                            class="btn btn-primary"
                            type="submit"
                            aria-label="{{__('stationboard.submit-search')}}"
                        >
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                @if($users->count() === 0 && request('searchQuery'))
                    <div class="alert alert-danger" role="alert">
                        {{ __('user.no-user') }}
                    </div>
                @endif
                @foreach($users as $user)
                    <div class="card status mt-3">
                        <div class="card-body row">
                            <div class="col-2 image-box search-image-box d-lg-flex">
                                <a href="{{ route('profile', ['username' => $user->username]) }}">
                                    <img
                                        src="{{resolve(\App\Services\ProfilePictureService::class)->getUrl($user)}}"
                                        alt="Profile picture"
                                        loading="lazy"
                                        decoding="async"/>
                                </a>
                            </div>

                            <div class="col ps-0">
                                <span class="float-end mt-3">
                                    @include('includes.follow-button')
                                </span>
                                <a href="{{ route('profile', ['username' => $user->username]) }}"
                                   style="font-size: calc(1.26rem + .12vw)">
                                    {{ $user->name }}
                                    @if($user->private_profile)
                                        <i class="fas fa-user-lock"></i>
                                    @endif
                                    <small class="text-muted">{{ '@' . $user->username }}</small>
                                </a>
                                <br/>
                                <span style="font-size: 0.875em;">
                                    <span class="font-weight-bold">
                                        <i class="fa fa-route d-inline"></i>
                                        {{ number($user->train_distance / 1000) }}
                                    </span>
                                    <span class="small font-weight-lighter">km</span>
                                    <span class="font-weight-bold ps-sm-2">
                                        <i class="fa fa-stopwatch d-inline"></i>
                                        {!! durationToSpan(secondsToDuration($user->train_duration * 60)) !!}
                                    </span>
                                    <span class="font-weight-bold ps-sm-2">
                                        <i class="fa fa-dice-d20 d-inline"></i>
                                        {{ $user->points }}
                                    </span>
                                    <span class="small font-weight-lighter">{{__('profile.points-abbr')}}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @if($users->count())
        <div class="row justify-content-center mt-5">
            {{ $users?->withQueryString()?->links() }}
        </div>
        @endif
    </div>
@endsection
