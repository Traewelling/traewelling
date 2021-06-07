@extends('layouts.app')

@section('title', $blogpost->title)

@section('meta-robots', 'index')
@section('meta-description', Markdown::parse($blogpost->preview))

@section('content')
    <div class="container blog">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <p><a href="{{route('blog.all')}}">{!! __('pagination.back') !!}</a></p>

                @if(substr(app()->getLocale(), 0, 2) != 'de')
                    <div class="alert alert-primary" role="alert" data-mdb-color="primary">
                        <i class="fas fa-globe"></i> {{__('localisation.not-available')}}
                    </div>
                @endif

                @include('blog.includes.blogpost', ['showFull' => true])
            </div>
        </div>
    </div>
@endsection
