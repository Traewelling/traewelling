@extends('layouts.minimal')

@section('code', '503')

@section('content')
    <div class="text-center">
        <h1 class="display-1 mb-4">503</h1>
        <h2 class="mb-3">
            <i class="fa-solid fa-wrench"></i>
            {{ __('maintenance') }}
        </h2>
        <p class="lead text-muted">
            {{__('maintenance.title')}} {{__('try-later')}}
        </p>
    </div>
    <hr/>
@endsection
