@extends('layouts.minimal')

@section('code', '503')

@section('content')
    <div class="text-center">
        <h1 class="display-1 mb-4">503</h1>
        <h2 class="mb-3">
            <i class="fa-solid fa-wrench"></i>
            {{ __('maintenance.title') }}
        </h2>
        <p class="lead text-muted">
            {{__('maintenance.subtitle')}} {{__('maintenance.try-later')}} :)<br><br>
            {{__('maintenance.prolonged')}}
        </p>
    </div>
    <hr/>
@endsection
