@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Status bearbeiten</h5>
                    <form method="GET" action="{{route('admin.status.edit')}}">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label class="form-label" for="form-statusId">Status ID?</label>
                            </div>
                            <div class="col-auto">
                                <input type="number" id="form-statusId" class="form-control" name="statusId"/>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Bearbeiten</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
