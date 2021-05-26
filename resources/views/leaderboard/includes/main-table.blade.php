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
                    <a href="{{ route('account.show', ['username' => $row->username]) }}">
                        {{ $row->username }}
                    </a>
                </td>
                <td>{!! durationToSpan(secondsToDuration(60 * $row->train_duration)) !!}</td>
                <td>{{ number($row->train_distance) }}<small>km</small></td>
                <td>{{ number($row->train_speed) }}<small>km/h</small></td>
                <td>{{ number($row->points, 0) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>