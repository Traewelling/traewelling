@extends('layouts.app')

@section('title')
    User
@endsection

@section('content')
    @include('includes.message-block')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <header><h3>Profile of {{ $user->username }}</h3></header>
                <form action="{{ route('follow.create') }}" method="post">
                    <button type="submit" class="btn btn-primary">Follow</button>
                    <input type="hidden" value="{{ $user->id }}" name="follow_id">
                    <input type="hidden" value="{{ Session::token() }}" name="_token">
                </form>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <header><h3>What {{ $user->name }} says...</h3></header>
                @foreach($statuses as $status)
                    <div class="row">
                        <div class="col">
                            <div class="card post" data-postid="{{ $status->id }}">
                                <div class="card-body">
                                    <p class="card-text">{{ $status->body }}</p>
                                </div>
                                <div class="card-footer text-muted interaction">
                                    Postet by <a href="{{ route('account.show', ['username' => $status->user->username]) }}">{{ $status->user->username }}</a> on {{ $status->created_at }} <br>
                                    <a href="#" class="like">Like</a> |
                                    <a href="#" class="like">Dislike</a>
                                    @if(Auth::user() == $status->user)
                                        |
                                        <a href="#" class="edit">Edit</a> |
                                        <a href="#" class="delete">Delete</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>

@endsection
