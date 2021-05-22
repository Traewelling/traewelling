@extends('layouts.app')

@section('title', __('menu.blog') . ': ' . $category)

@section('meta-robots', 'index')
@section('meta-description', __('description.blog.category', ['category' => $category]))

@section('content')
    <div class="container blog">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <p><a href="{{route('blog.all')}}">{!! __('pagination.back') !!}</a></p>
                <h1 style="font-size: 1.8em;"><i class="fa fa-tag pe-2"></i>{{$category}}</h1>

                @foreach($blogposts as $blogpost)
                    <hr class="w-100"/>
                    @include('blog.includes.blogpost', ['showFull' => true])
                @endforeach
            </div>
        </div>
        <div class="row justify-content-center">
            {{ $blogposts->links() }}
        </div>
    </div>
@endsection
