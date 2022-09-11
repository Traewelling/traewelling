@extends('admin.layout')

@section('title', 'Locations')

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <a href="{{route('admin.locations.create')}}" class="btn btn-sm btn-success float-end">
                <i class="fas fa-plus" aria-hidden="true"></i>
                New
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($locations as $location)
                            <tr>
                                <td>{{$location->id}}</td>
                                <td>{{$location->name}}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{route('admin.locations.delete')}}">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$location->id}}"/>
                                        <div class="btn-group">
                                            <a href="{{route('admin.locations.edit', ['id' => $location->id])}}"
                                               class="btn btn-sm btn-primary">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{$locations->links()}}
        </div>
    </div>
@endsection
