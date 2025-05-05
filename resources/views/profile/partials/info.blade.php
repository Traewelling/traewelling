@php /** @var \App\Models\User $user */ @endphp
<div class="card mb-4">
    <div class="card-header">
        {{ __('profile.statistics') }}
    </div>

    <ul class="list-group list-group-flush">
        <li class="list-group-item">
            <span class="font-weight-bold">
                <i class="fa fa-route d-inline"></i>&nbsp;{{ number($user->train_distance / 1000) }}
            </span>
            <span class="small font-weight-lighter">km</span>
        </li>
        <li class="list-group-item">
            <span class="font-weight-bold">
                <i class="fa fa-stopwatch d-inline"></i>&nbsp;{!! durationToSpan(secondsToDuration($user->train_duration * 60)) !!}
            </span>
        </li>
        @if($user->points_enabled || auth()->check() && auth()->user()->points_enabled)
            <li class="list-group-item">
                <span class="font-weight-bold">
                    <i class="fa fa-dice-d20 d-inline"></i>&nbsp;{{ $user->points }}
                </span>
                <span class="small font-weight-lighter">
                    {{__('profile.points-abbr')}}
                </span>
            </li>
        @endif
        @if($user->mastodonUrl)
            <li class="list-group-item">
                <span class="font-weight-bold ps-sm-2">
                    <a href="{{ $user->mastodonUrl }}" rel="me" class="text-white" target="_blank">
                        <i class="fab fa-mastodon d-inline"></i>
                    </a>
                </span>
            </li>
        @endif
    </ul>
</div>

<div class="card mb-4">
    <div class="card-header">
        {{ __('profile.info') }}
    </div>
    <div class="card-body">
        <h5>{{ __('profile.bio') }}</h5>
        <p>Hallo, ich bin ein lustiger, typ, der hier eine Bio an seinem Profil hinterlegt hat. Das sieht alles sehr
            funny aus. Blah Blah Blah.</p>
        <h5>{{ __('welcome.footer.social') }}</h5>
        @php /** @var \App\Models\ProfileLink $link */ @endphp
        @foreach($user->profileLinks as $link)
            <div class="btn-broup shadow-none">
                <a href="{{ $link->url }}" class="btn btn-sm" target="_blank">
                    <i class="{{ $link->name->getIcon() }}"></i>
                </a>
            </div>
        @endforeach
    </div>
</div>
