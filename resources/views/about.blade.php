@extends('layouts.app')

@section('title', 'FAQ - ' . __('about.faq-heading'))
@section('meta-robots', 'index')
@section('meta-description', __('about.block1'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8" id="about-page">
                <h1>{{ __('about.heading') }}</h1>
                <blockquote class="blockquote"><p>{{ __('about.block1') }}</p></blockquote>

                <h2>F.A.Q. <small class="text-muted">{{ __('about.faq-heading') }}</small></h2>

                <h3>{{ __('about.who-heading') }}</h3>
                <p class="lead">{{ __('about.who') }} <a href="{{ url('humans.txt') }}">humans.txt</a></p>

                <h3>{{ __('about.name-heading') }}</h3>
                <p class="lead">{!! __('about.name') !!}</p>

                <h3>{{ __('about.no-train-heading') }}</h3>
                <p class="lead">{{ __('about.no-train') }}</p>

                <h3>{{ __('about.feature-missing-heading') }}</h3>
                <p class="lead">{{ __('about.feature-missing') }} <a
                            href="mailto:hi@traewelling.de">hi@traewelling.de</a>.</p>

                <h3>{{ __('about.points-heading') }}</h3>
                <p class="lead">
                    {{ __('about.points1') }}
                </p>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('about.productclass') }}</th>
                            <th scope="col">{{ __('about.basepoints') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">{{ __('about.tram') }}</th>
                            <td>2</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ __('about.suburban') }}</th>
                            <td>3</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ __('about.regional') }}</th>
                            <td>5</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ __('about.express') }}</th>
                            <td>10</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ __('about.international') }}</th>
                            <td>10</td>
                        </tr>
                    </tbody>
                </table>

                <p class="lead">{!! __('about.calculation') !!}</p>

            </div>
@endsection
