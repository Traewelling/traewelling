@extends('layouts.settings')
@section('title', __('settings.tab.account'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            @include('settings.cards.password')
            @include('settings.cards.account-deletion')
        </div>
    </div>
@endsection
