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
                <?php $d = ""; ?>
                @foreach($statuses as $status)
                    <?php $newD = date('Y-m-d', strtotime($status->trainCheckin->departure)); ?>
                    @if($newD != $d)
                        <?php
                        $d = $newD;
                        $dtObj = new \DateTime($status->trainCheckin->departure);
                        ?>
                        <h5 class="mt-4">{{__($dtObj->format('l')) }}, {{ $dtObj->format('j') }}. {{__($dtObj->format('F')) }} {{ $dtObj->format('Y') }}</h5>
                    @endif
                    @include('includes.status')
                @endforeach

            </div>
        </div>
    </div>

@endsection
