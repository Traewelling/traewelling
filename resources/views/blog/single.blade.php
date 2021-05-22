@extends('layouts.app')

@section('title', $blogpost->title)

@section('content')
    <div class="container blog">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <p><a href="{{route('blog.all')}}">{!! __('pagination.back') !!}</a></p>

                @include('blog.includes.blogpost', ['showFull' => true])
            </div>
        </div>
    </div>
@endsection
