@extends('admin.layout')

@section('title', 'Licenses & Sources')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.sources') }}">Sources</a> | <a href="{{ route('licenses.index') }}" class="text-muted">Licenses</a>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">New License (all fields are nullable)</h5>
                    <form method="POST" action="{{ route('licenses.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col">
                                <input class="form-control" placeholder="Source Name" name="name">
                            </div>
                            <div class="col">
                                <input class="form-control" placeholder="Human Name" name="human_name">
                            </div>
                            <div class="col">
                                <input class="form-control" placeholder="Attribution (empty for none)"
                                       name="attribution">
                            </div>
                            <div class="col-1">
                                <select class="form-select" name="automatically_activate_source">
                                    <option value="">Force Active</option>
                                    <option
                                        value="1">
                                        Yes
                                    </option>
                                    <option
                                        value="0">
                                        No
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col">
                                <input class="form-control" placeholder="License URL" name="license_url">
                            </div>
                            <div class="col">
                                <input class="form-control" placeholder="Source URL" name="source_url">
                            </div>
                            <div class="col-1">
                                <input class="form-control" placeholder="SPDX ID" name="spdx_id">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary mt-3">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
