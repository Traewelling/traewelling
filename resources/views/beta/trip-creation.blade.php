@extends('layouts.app')

@section('title', 'Create trip manually')

@section('content')
    <div class="container">
        <div id="trip-creation-form">
            @if(app()->getLocale() !== 'en')
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i>
                    {{__('page-only-available-in-language', ['language' => __('language.en')])}}
                </div>
            @endif

            <trip-creation-form></trip-creation-form>
        </div>
    </div>
@endsection
