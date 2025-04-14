@extends('admin.layout')

@section('title', 'Licenses & Sources')

@section('content')

    @php
        /**
        * @var \App\Models\MotisSourceLicense[] $sources
        * @var \App\Models\License[] $licenses
        * @var \App\Http\Requests\admin\SourceIndexFilterRequest $filter
        */
    @endphp
    <div class="mb-3">
        <a href="{{ route('admin.sources') }}" class="text-muted">Sources</a> | <a href="{{ route('licenses.index') }}">Licenses</a>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Filters</h5>
                    <form>
                        <div class="row">
                            <div class="col">
                                <input class="form-control" placeholder="Countrycode" name="country"
                                       value="{{ $filter->country }}">
                            </div>
                            <div class="col">
                                <input class="form-control" placeholder="Source Name" name="name"
                                       value="{{ $filter->name }}">
                            </div>
                            <div class="col">
                                <input class="form-control" placeholder="Human Name" name="human_name"
                                       value="{{ $filter->human_name }}">
                            </div>
                            <div class="col-1">
                                <select class="form-select" name="active">
                                    <option value="">Active</option>
                                    <option value="1" {{ $filter->active === 1 ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ $filter->active === 0 ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary mt-3">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.sources.mass-assign') }}">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-12 mb-2">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Mass assign</h5>
                        <div class="d-flex justify-content-between">
                            <select class="form-select" name="license_id">
                                <option value="">Select License</option>
                                @foreach($licenses as $license)
                                    <option value="{{ $license->id }}">{{ $license->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">Assign</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" aria-labelledby="pageTitle">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>Country</th>
                                    <th>Source Name</th>
                                    <th>Human Name</th>
                                    <th>License</th>
                                    <th>Source</th>
                                    <th>SPDX</th>
                                    <th>Manual License</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sources as $source)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="source_ids[]" value="{{ $source->id }}">
                                        </td>
                                        <td>{{ $source->country }}</td>
                                        <td>{{ $source->name }}</td>
                                        <td>{{ $source->human_name }}</td>
                                        @if ($source->license_url)
                                            <td><a href="{{$source->license_url}}" target="_blank">Link</a></td>
                                        @else
                                            <td>-</td>
                                        @endif
                                        @if ($source->source_url)
                                            <td><a href="{{$source->source_url}}" target="_blank">Link</a></td>
                                        @else
                                            <td>-</td>
                                        @endif
                                        <td>{{ $source->spdx }}</td>
                                        <td>{{ $source->manualLicense?->name }}</td>
                                        <td>{{ $source->active }}</td>
                                        <td class="text-end">
                                            {{--                                            <a href="{{ route('admin.sources.show', ['id' => $source->id]) }}"--}}
                                            {{--                                               class="btn btn-primary btn-sm">--}}
                                            {{--                                                Edit--}}
                                            {{--                                            </a>--}}
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
    </form>
    {{ $sources->links() }}

@endsection
