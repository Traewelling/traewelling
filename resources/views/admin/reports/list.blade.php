@extends('admin.layout')

@section('title', 'Reports')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>ID</th>
                            <th>Reporter</th>
                            <th>Subject</th>
                            <th>Reason</th>
                            <th>Description</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                            <tr>
                                <td>
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
                                </td>
                                <td>#{{ $report->id }}</td>
                                <td>
                                    @isset($report->reporter)
                                        <a href="{{ route('admin.users.user', $report->reporter->id) }}">
                                            {{ $report->reporter->name}}<br/>
                                            <small>{{ '@' . $report->reporter->username }}</small>
                                        </a>
                                    @endisset
                                </td>
                                <td>
                                    <a href="{{ route('admin.activity', ['subject_type' => $report->subject_type, 'subject_id' => $report->subject_id]) }}">
                                        {{ class_basename($report->subject_type) }} #{{ $report->subject_id }}
                                    </a>
                                </td>
                                <td>{{ $report->reason }}</td>
                                <td><code>{{ $report->description }}</code></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.reports.show', $report->id) }}"
                                       class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i>
                                        View
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-primary edit-close"
                                       data-id="{{ $report->id }}">
                                        <i class="fa fa-close"></i>
                                        Close
                                    </a>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>

                {{ $reports->links() }}
            </div>
        </div>
    </div>

    <script>
        let closeButtons = document.getElementsByClassName('edit-close');

        let closeFunction = function () {
            let id = this.getAttribute('data-id');

            if (confirm('Are you sure you want to close report #' + id + '?')) {
                fetch('/api/v1/report/' + id, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        status: 'closed',
                        description: 'Closed by admin'
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
            }
        }

        Array.from(closeButtons).forEach(function (element) {
            element.addEventListener('click', closeFunction);
        });
    </script>
@endsection
