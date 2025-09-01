@php /** @var \App\Models\User $user */ @endphp
<div class="card mb-3 shadow-sm rounded">
    <div class="card-body">
        <div class="row text-center gx-2 gy-3">
            <div class="col">
                <i class="fa fa-route fa-2x text-trwl"></i>
                <div class="h5 mb-0">
                    {{ number($user->train_distance / 1000, 1) }}
                    <small class="text-muted">km</small>
                </div>
            </div>
            <div class="col">
                <i class="fa fa-stopwatch fa-2x text-trwl"></i>
                <div class="h5 mb-0">
                    {!! durationToSpan(secondsToDuration($user->train_duration * 60)) !!}
                </div>
            </div>
            @if($user->points_enabled || (auth()->check() && auth()->user()->points_enabled))
                <div class="col">
                    <i class="fa fa-dice-d20 fa-2x text-trwl"></i>
                    <div class="h5 mb-0">{{ $user->points }}
                        <small class="text-muted">{{ __('profile.points-abbr') }}</small>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@php
    use App\Enum\ProfileLinkName;
    use App\Models\ProfileLink;

    // workaround because of duplicate mastodon handling (TODO: Remove when mastodon is included in profileLinks)
    $links = $user->profileLinks->toBase();

    if ($user->mastodonUrl
        && ! $links->contains('name', ProfileLinkName::MASTODON)
    ) {
        $links->push(new ProfileLink([
            'user_id' => $user->id,
            'name'    => ProfileLinkName::MASTODON,
            'url'     => $user->mastodonUrl,
        ]));
    }
@endphp
@if($user->bio || $links->isNotEmpty())
    <div class="card mb-3 shadow-sm rounded">
        <div class="card-body">

            @if($user->bio)
                <p class="text-muted fst-italic m-0">
                    <i class="fa fa-quote-left me-2"></i>
                    <span class="profile-bio">{{ $user->bio }}</span>
                </p>
            @endif

            @if($links->isNotEmpty())
                <div class="d-flex justify-content-center flex-wrap gap-3 mt-4">
                    @foreach($links as $link)
                        <a href="{{ $link->url }}"
                           class="text-muted fs-4"
                           aria-label="{{ $link->name->getName() }}"
                           target="_blank"
                           rel="me">
                            <i class="{{ $link->name->getIcon() }}"></i>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
@endif

