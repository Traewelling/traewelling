@extends('layouts.app')
@section('title')
@if($page == "home")
    {{__('menu.blog')}}
@elseif($page == "single")
    {{ $blogposts[0]->title }}
@elseif($page == "category")
    {{__('menu.blog')}}: {{ $category }}
@endif
@endsection
@section('content')
    <div class="container blog">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if($page == "home")
            <h1>{{__('menu.blog')}}</h1>

            @elseif($page == "single")
            <p><a href="{{route('blog.all')}}">{!! __('pagination.back') !!}</a></p>

            @elseif($page == "category")

            <p><a href="{{route('blog.all')}}">{!! __('pagination.back') !!}</a></p>
            <h1><i class="fa fa-tag pr-2"></i>{{$category}}</h1>
            @endif

            @foreach($blogposts as $blogpost)
            <div class="card mb-4">
                <div class="card-header">
                    <p class="mb-0 float-right">
                        <code class="pr-2">{{ $blogpost->published_at->format('d.m.Y') }}</code>
                    </p>
                    <a href="{{ route('blog.show', ['slug' => $blogpost->slug]) }}">
                        <h3 class="mb-0">{{$blogpost->title}}</h3>
                    </a>
                </div>
                <div class="card-body">
                    {!! Markdown::parse($blogpost->body) !!}
                </div>
                <div class="card-footer text-muted">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item">
                            @if(!empty($blogpost->twitter_handle))
                                <a href="https://twitter.com/{{ $blogpost->twitter_handle }}">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            @endif

                            {{ $blogpost->author_name }}
                        </li>
                        <li class="list-inline-item">
                            <a href="{{ route('blog.category', ['category' => $blogpost->category]) }}">
                                <i class="fa fa-tag"></i>
                                {{$blogpost->category}}
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($blogpost->title . ' ' . route('blog.show', ['slug' => $blogpost->slug]) . ' via @traewelling' ) }}">Tweet</a>
                        </li>
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="row justify-content-center">
        {{ $blogposts->links() }}
    </div>
</div>
@endsection
