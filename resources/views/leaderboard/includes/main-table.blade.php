<table class="table table-striped table-hover" aria-describedby="{{$describedBy}}">
    <thead>
        <tr>
            <th scope="col">{{ __('leaderboard.rank') }}</th>
            <th scope="col">{{ __('leaderboard.user') }}</th>
            <th scope="col">{{ __('leaderboard.duration') }}</th>
            <th scope="col">{{ __('leaderboard.distance') }}</th>
            <th scope="col">{{ __('leaderboard.averagespeed') }}</th>
            <th scope="col">{{ __('leaderboard.points') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td>{{ $loop->index + 1 }}</td>
                <td>
                    <a href="{{ route('profile', ['username' => $row->user->username]) }}">
                        {{ $row->user->username }}
                    </a>
                </td>
                <td>{!! durationToSpan(secondsToDuration(60 * $row->duration)) !!}</td>
                <td>{{ number($row->distance / 1000) }}<small>km</small></td>
                <td>{{ number($row->speed / 1000) }}<small>km/h</small></td>
                <td>{{ number($row->points, 0) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
