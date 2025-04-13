@extends('admin.layout')

@section('title', 'Licenses & Sources')

@section('content')

    @php
        /** @var \App\Models\MotisSourceLicense[] $licenses
        * @var \App\Http\Requests\admin\LicenseIndexFilterRequest $filter
        */
    @endphp
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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" aria-labelledby="pageTitle">
                            <thead>
                            <tr>
                                <th>Country</th>
                                <th>Source Name</th>
                                <th>Human Name</th>
                                <th>License</th>
                                <th>SPDX</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($licenses as $license)
                                <tr>
                                    <td>{{ $license->country }}</td>
                                    <td>{{ $license->name }}</td>
                                    <td>{{ $license->human_name }}</td>
                                    @if ($license->license_url)
                                        <td><a href="{{$license->license_url}}" target="_blank">Link</a></td>
                                    @else
                                        <td>-</td>
                                    @endif
                                    <td>{{ $license->spdx }}</td>
                                    <td>{{ $license->active }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.license.show', ['id' => $license->id]) }}"
                                           class="btn btn-primary btn-sm">
                                            Edit
                                        </a>
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
