@extends('layouts.admin')

@section('title', 'Status')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Status bearbeiten</div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{route('admin.status.edit')}}">
                        <div class="row g-1">
                            <div class="col">
                                <div class="form-outline mb-4">
                                    <input type="number" id="form-statusId" class="form-control" name="statusId"/>
                                    <label class="form-label" for="form-statusId">Status ID?</label>
                                </div>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary btn-block">Bearbeiten</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
