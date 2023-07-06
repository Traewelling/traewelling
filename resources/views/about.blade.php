@extends('layouts.app')

@section('title', 'FAQ - ' . __('about.faq-heading'))
@section('meta-robots', 'index')
@section('meta-description', __('about.block1'))
@section('canonical', route('static.about'))

@section('content')
    <div class="px-4 py-5 mt-n4 mb-4 profile-banner">
        <div class="container">
            <div class="text-white">
                <h1>F.A.Q. <br/><span class="fs-3">{{ __('about.faq-heading') }}</span></h1>

                <hr/>
                <div class="btn-group">
                    <a href="https://github.com/Traewelling/traewelling/issues/new?assignees=&labels=bug%2CTo+Do&template=bug_report.md"
                       target="_blank" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-bug"></i>
                        {{__('report-bug')}}
                    </a>
                    <a href="https://github.com/Traewelling/traewelling/issues/new?assignees=&labels=enhancement&template=feature_request.md&title="
                       target="_blank" class="btn btn-sm btn-success">
                        <i class="fa-solid fa-plus"></i>
                        {{__('request-feature')}}
                    </a>
                    <a href="{{route('support')}}" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-headset"></i>
                        {{__('to-support')}}
                        @guest
                            {{__('login-required')}}
                        @endguest
                    </a>
                </div>
            </div>


        </div>
    </div>

    <div class="container" id="about-page">
        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-circle-question"></i>
                            {{ __('about.heading') }}
                        </h2>
                        <p class="lead m-0">{{ __('about.block1') }}</p>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-person-circle-question"></i>
                            {{ __('about.who-heading') }}
                        </h2>
                        <p class="lead m-0">
                            {{ __('about.who0') }}
                            {{ __('about.who1') }}
                            {!! __('about.who2', ['link' => 'https://github.com/Traewelling/traewelling/graphs/contributors']) !!}
                            {!! __('about.who3', ['link' => url('humans.txt')]) !!}
                        </p>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-code"></i>
                            {{ __('about.dev') }}
                        </h2>
                        <p class="lead m-0">
                            {{ __('about.dev.1') }}
                            {{ __('about.dev.2') }}
                            {{ __('about.dev.3') }}
                            {{ __('about.dev.4') }}
                            {!! __('about.dev.5', ['link' => 'https://discord.gg/QypAnG2qAw']) !!}
                        </p>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-comment-dots"></i>
                            {{ __('about.name-heading') }}
                        </h2>
                        <p class="lead m-0">{!! __('about.name') !!}</p>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-calendar"></i>
                            {{ __('about.events') }}
                        </h2>
                        <p class="lead mb-2">{{__('about.events.description1')}}</p>
                        <p class="lead mb-2">{!! __('about.events.description2', ['link' => route('events')]) !!}</p>
                        <p class="lead mb-2">{!! __('about.events.description3') !!}</p>
                        <p class="lead mb-2">{{__('about.events.description4')}}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-at"></i>
                            {{ __('about.missing-verification-email') }}
                        </h2>
                        <p class="lead m-0">
                            {{ __('about.missing-verification-email.description') }}
                            {{ __('about.missing-verification-email.description2', ['email' => config('mail.from.address')]) }}
                            {{ __('about.missing-verification-email.description3') }}
                        </p>
                        <hr/>
                        <p>
                            <i class="fa-solid fa-triangle-exclamation text-trwl"></i>
                            {{ __('about.missing-verification-email.description4') }}
                            {{ __('about.missing-verification-email.description5') }}
                        </p>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-train"></i>
                            {{ __('about.no-train-heading') }}
                        </h2>
                        <p class="lead m-0">{{ __('about.no-train') }}</p>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl" id="heading-points">
                            <i class="fa-solid fa-gem"></i>
                            {{ __('about.points-heading') }}
                        </h2>
                        <p class="lead mb-2">{{ __('about.points1') }}</p>
                        <table class="table table-hover table-striped" aria-describedby="heading-points">
                            <thead>
                                <tr>
                                    <th scope="col" class="fw-bold">{{ __('about.productclass') }}</th>
                                    <th scope="col" class="fw-bold">{{ __('about.basepoints') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Enum\HafasTravelType::cases() as $category)
                                    <tr>
                                        <th scope="row">{{ __('transport_types.' . $category->value) }}</th>
                                        <td>{{config('trwl.base_points.train.' . $category->value, 1)}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <p class="lead mb-2">{!! __('about.calculation') !!}</p>

                        <p class="lead text-danger mt-2">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            {{__('about.points-real-time')}}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
