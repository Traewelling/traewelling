@extends('admin.layout')

@section('title', 'Activity log')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.activity.table')
                    {{$activities->appends(request()->input())->links()}}
                </div>
            </div>
        </div>
    </div>
@endsection
