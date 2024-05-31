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

                @foreach($changelog['entry'] ?? [] as $changelogEntry)
                    <hr/>
                    <h2 class="mb-0">{{$changelogEntry['title'] ?? ''}}</h2>
                    <small>released on {{$changelogEntry['updated']->isoFormat(__('date-format'))}}</small>
                    {!! $changelogEntry['content'] ?? '' !!}
                @endforeach
            </div>
        </div>
    </div>
@endsection
