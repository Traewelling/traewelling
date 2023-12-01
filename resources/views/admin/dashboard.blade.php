@extends('admin.layout')

@section('content')
    <div class="text-center text-white">
        <i class="fa-solid fa-hammer fa-5x"></i>
        <h1 class="mt-3 mb-3 fs-3" id="pageTitle">
            Welcome dear
            @if(auth()->user()->hasRole('admin'))
                Admin!
            @elseif(auth()->user()->hasRole('event-moderator'))
                Event Moderator!
            @else
                Hacker!
            @endif
            <br/>
            Don't do anything stupid!
        </h1>
    </div>
@endsection
