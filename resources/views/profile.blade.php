@extends('layouts.app')

@section('title')
    User
@endsection

@section('content')
    <div class="jumbotron mt-n4" style="background-image: url(https://mobile.traewelling.de/src/img/cover.png);background-position: center;">
        <div class="container">
            <img src="/uploads/avatars/{{ $user->avatar }}" height="20%" width="20%" class="float-right img-thumbnail rounded-circle img-fluid"><div class="text-white px-4">
                    <h2 class="card-title h1-responsive font-bold">
                        <strong>{{ __('Statistics for ') }} {{ $user->name }}</strong> <small class="font-weight-light">{{ '@'.$user->username }}</small>
                        @if($user != Auth::user() && Auth::check())
                            <a href="#" class="btn btn-sm btn-primary follow" data-userid="{{ $user->id }}"
                            @if(Auth::user()->follows->where('follow_id', $user->id)->first() === null)
                               data-following="no">Follow</a>
                            @else
                                data-following="yes">Unfollow</a>
                            @endif
                        @else
                            <a href="{{ route('settings') }}" class="btn btn-sm btn-primary">{{ __('Settings') }}</a>
                        @endif
                    </h2>
                    <h2>
                        <span class="font-weight-bold">{{ round($user->train_distance, 2) }}</span><span class="small font-weight-lighter">km</span>&nbsp;
                        <span class="font-weight-bold">{{ date('H:i', mktime(0,$user->train_duration)) }}</span><span class="small font-weight-lighter">h</span>&nbsp;
                        <span class="font-weight-bold">{{ $user->points }}</span><span class="small font-weight-lighter">Pkt.</span>&nbsp;
                    </h2>
            </div>
        </div>
    </div>
    @include('includes.message-block')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <header><h3>&nbsp;</h3></header>
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
        <div class="row justify-content-center mt-5">
            {{ $statuses->links() }}
        </div>
        @include('includes.edit-modal')
        @include('includes.delete-modal')
    </div>
@endsection
