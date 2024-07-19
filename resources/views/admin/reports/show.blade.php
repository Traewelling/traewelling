@extends('admin.layout')

@section('title', 'Report ' . $report->id)

@section('content')
    @php /** @var \App\Models\Report $report */ @endphp
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-5">
                        <i class="fa fa-flag"></i>
                        Report details
                    </h2>

                    <dl>
                        <dt>ID</dt>
                        <dd>#{{ $report->id }}</dd>
                        <dt>Reporter</dt>
                        <dd>
                            @isset($report->reporter)
                                <a href="{{ route('admin.users.user', $report->reporter->id) }}">
                                    {{ $report->reporter->name}}<br/>
                                    <small>{{ '@' . $report->reporter->username }}</small>
                                </a>
                            @endisset
                        </dd>

                        <dl>
                            <dt>Subject</dt>
                            <dd>
                                @if( $report->subject_type === \App\Models\Trip::class )
                                    <a href="{{ route('admin.trip.show', $report->subject_id) }}">
                                        Trip #{{ $report->subject_id }}
                                    </a>
                                @elseif( $report->subject_type === \App\Models\User::class )
                                    <a href="{{ route('admin.users.user', $report->subject_id) }}">
                                        User #{{ $report->subject_id }}
                                    </a>
                                @elseif( $report->subject_type === \App\Models\Event::class )
                                    <a href="{{ route('admin.events.edit', $report->subject_id) }}">
                                        Event #{{ $report->subject_id }}
                                    </a>
                                @elseif( $report->subject_type === \App\Models\Status::class )
                                    <a href="{{ route('admin.status.edit', ['statusId' => $report->subject_id]) }}">
                                        Status #{{ $report->subject_id }}
                                    </a>
                                @endif
                            </dd>
                        </dl>
                        <dt>Activity</dt>
                        <dd>
                            <a href="{{ route('admin.activity', ['subject_type' => $report->subject_type, 'subject_id' => $report->subject_id]) }}">
                                {{ class_basename($report->subject_type) }} #{{ $report->subject_id }}
                            </a>
                        </dd>
                        <dt>Reason</dt>
                        <dd>{{ $report->reason }}</dd>
                        <dt>Description</dt>
                        <dd><code>{{ $report->description }}</code></dd>
                        <dt>Status</dt>
                        <dd>
                            @if($report->status === \App\Enum\Report\ReportStatus::OPEN)
                                <span class="badge bg-danger">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    Open
                                </span>
                            @elseif($report->status === \App\Enum\Report\ReportStatus::WAITING)
                                <span class="badge bg-warning">
                                    <i class="fa fa-clock"></i>
                                    Waiting
                                </span>
                            @elseif($report->status === \App\Enum\Report\ReportStatus::CLOSED)
                                <span class="badge bg-success">
                                    <i class="fa fa-check"></i>
                                    Closed
                                </span>
                            @endif
                        </dd>
                        <dt>Created at</dt>
                        <dd>{{ userTime($report->created_at, 'Y-m-d H:i:s', false) }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-5">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Subject
                    </h2>


                    <code>ToDo: Show subject details depending on subject type</code>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-5">
                        <i class="fa fa-comments"></i>
                        Actions
                    </h2>

                    @include('admin.activity.table', ['activities' => $report->activities])
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="fs-5">
                        <i class="fa fa-hammer"></i>
                        Do some action
                    </h2>

                    <form id="form-report-action">
                        <div class="mb-3 form-floating">
                            <select class="form-select" name="status" id="status">
                                @foreach(\App\Enum\Report\ReportStatus::cases() as $status)
                                    <option
                                        value="{{ $status->value }}"
                                        {{ $report->status === $status ? 'selected' : '' }}
                                    >
                                        {{ $status->value }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="status" class="form-label">New Status</label>
                        </div>
                        <div class="mb-3 form-floating">
                            <textarea class="form-control" name="description" id="description"
                                      rows="3"></textarea>
                            <label for="description" class="form-label">Description</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                    <script>
                        document.getElementById('form-report-action').addEventListener('submit', function (event) {
                            event.preventDefault();

                            const status      = document.querySelector('#form-report-action select[name="status"]').value;
                            const description = document.querySelector('#form-report-action textarea[name="description"]').value;

                            fetch('/api/v1/report/{{$report->id}}', {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    status, description
                                }),
                            }).then(response => {
                                if (response.status === 200) {
                                    window.location.reload();
                                    return;
                                }
                                response.json().then(data => {
                                    alert(data.message ?? 'Something went wrong. Please try again later.');
                                });
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
@endsection
