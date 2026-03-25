@props([
'$changelog' => []
])
@extends('layouts.app')

@section('title', __('changelog'))
@section('canonical', route('changelog'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <h1>{{__('changelog')}}</h1>

                @if(app()->getLocale() !== 'en')
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i>
                        {{__('page-only-available-in-language', ['language' => __('language.en')])}}
                    </div>
                @endif

                @foreach($changelog as $changelogEntry)
                    <hr/>
                    <a target="_blank" rel="noopener noreferrer" href="https://github.com/Traewelling/traewelling/releases/tag/{{$changelogEntry->tag}}">
                        <h1 class="mb-0">
                        {{$changelogEntry->title}}
                        </h1>
                    </a>
                    <small>released on {{$changelogEntry->created->isoFormat(__('date-format'))}}</small>
                    {!! markdown($changelogEntry->description) !!}
                @endforeach
            </div>
        </div>
    </div>
@endsection
