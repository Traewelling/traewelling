@extends('layouts.app')

@section('title', __('menu.settings'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7">
                @include('settings.cards.general')
                @include('settings.cards.privacy')
                @include('settings.cards.password')
                @include('settings.cards.login-providers')
                @include('settings.cards.sessions')
                @include('settings.cards.ics')
                @include('settings.cards.account-deletion')
            </div>
        </div>
    </div>
@endsection
