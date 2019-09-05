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
                @if($user != Auth::user() && Auth::check())
                <form action="{{ Auth::user()->follows->where('follow_id', $user->id)->first() === null ? route('follow.create') : route('follow.destroy') }}" method="post">
                    <button type="submit" class="btn btn-primary">{{ Auth::user()->follows->where('follow_id', $user->id)->first() === null ? 'Follow' : 'Unfollow' }}</button>
                    <input type="hidden" value="{{ $user->id }}" name="follow_id">
                    <input type="hidden" value="{{ Session::token() }}" name="_token">
                </form>
                @endif
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <header><h3>Last journeys of {{ $user->name }}:</h3></header>
                @foreach($statuses as $status)
                    @include('includes.status')
                @endforeach

            </div>
        </div>
    </div>

@endsection
