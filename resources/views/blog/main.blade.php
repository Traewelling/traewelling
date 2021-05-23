@extends('layouts.app')

@section('title', __('menu.blog'))

@section('meta-robots', 'index')
@section('meta-description', __('description.blog'))

@section('content')
    <div class="container blog">
        <div class="row justify-content-center">
            <div class="col-md-9">
                @foreach($blogposts as $blogpost)
                    @include('blog.includes.blogpost', ['showFull' => false])
                    <hr class="w-100"/>
                @endforeach
            </div>
        </div>
        <div class="row justify-content-center">
            {{ $blogposts->links() }}
        </div>
    </div>
@endsection
