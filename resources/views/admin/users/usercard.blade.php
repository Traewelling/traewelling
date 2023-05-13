<div class="card mb-2">
    <div class="card-body">
        <h4 class="card-title">{{ $user->name }} <small>@ {{ $user->username }}</small></h4>
        <div class="row border-bottom">
            <div class="col">
                Strecke <br>
                {{ number($user->train_distance / 1000) }}
            </div>
            <div class="col">
                Dauer <br>
                {!! durationToSpan(secondsToDuration($user->train_duration * 60)) !!}
            </div>
            <div class="col">
                Punkte <br>
                {{ $user->points }}
            </div>
        </div>
        <div class="row mt-1">
            <div class="col border-right">
                Mastodon<br>
                <strong>{{ $user->mastodonUrl ? 'Ja' : 'Nein' }}</strong>
            </div>
        </div>
    </div>
</div>
