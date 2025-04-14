@extends('admin.layout')

@section('title', 'Licenses & Sources')

@section('content')

    @php
        /**
        * @var \App\Models\License[] $licenses
        * @var \App\Http\Requests\admin\LicenseIndexFilterRequest $filter
        */
    @endphp
    <div class="mb-3">
        <a href="{{ route('admin.sources') }}">Sources</a> | <a href="{{ route('licenses.index') }}" class="text-muted">Licenses</a>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Filters</h5>
                    <form>
                        <div class="row">
                            <div class="col">
                                <input class="form-control" placeholder="Source Name" name="name"
                                       value="{{ $filter->name }}">
                            </div>
                            <div class="col">
                                <input class="form-control" placeholder="Human Name" name="human_name"
                                       value="{{ $filter->human_name }}">
                            </div>
                            <div class="col-1">
                                <select class="form-select" name="automatically_activate_source">
                                    <option value="">Active</option>
                                    <option
                                            value="1" {{ $filter->automatically_activate_source === 1 ? 'selected' : '' }}>
                                        Yes
                                    </option>
                                    <option
                                            value="0" {{ $filter->automatically_activate_source === 0 ? 'selected' : '' }}>
                                        No
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{route('licenses.create')}}" class="btn btn-outline-primary mt-3">New</a>
                            <button type="submit" class="btn btn-primary mt-3">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" aria-labelledby="pageTitle">
                            <thead>
                            <tr>
                                <th>Source Name</th>
                                <th>Human Name</th>
                                <th>Attribution</th>
                                <th>License</th>
                                <th>Source</th>
                                <th>Active</th>
                                <th>Automatically Activate Source</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($licenses as $license)
                                <tr>
                                    <td>{{ $license->name }}</td>
                                    <td>{{ $license->human_name }}</td>
                                    <td><input type="text" disabled value="{{ $license->attribution }}"/></td>
                                    @if ($license->license_url)
                                        <td><a href="{{$license->license_url}}" target="_blank">Link</a></td>
                                    @else
                                        <td>-</td>
                                    @endif
                                    @if ($license->source_url)
                                        <td><a href="{{$license->source_url}}" target="_blank">Link</a></td>
                                    @else
                                        <td>-</td>
                                    @endif
                                    <td>{{ $license->spdx }}</td>
                                    <td>{{ $license->automatically_activate_source }}</td>
                                    <td class="text-end">
                                        {{--                                        <a href="{{ route('admin.sources.show', ['id' => $license->id]) }}"--}}
                                        {{--                                           class="btn btn-primary btn-sm">--}}
                                        {{--                                            Edit--}}
                                        {{--                                        </a>--}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ $licenses->links() }}

@endsection
