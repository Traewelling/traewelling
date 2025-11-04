@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="container">
        <div id="vue-content">
            <{{$vueComponent}}></{{$vueComponent}}>
        </div>
    </div>
@endsection
