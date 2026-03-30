@props([
    'agreement',
    'hasUserSigned' => false,
    'policyChanged' => false,
    'user' => null,
])
@extends('layouts.app')

@section('title', html_entity_decode(__('privacy.title')))
@section('meta-robots', 'noindex')

@section('content')
    <style>
        .cookiealert { /* Wir wollen keine doppelten Bottom-Bars auf der Privacy-Seite. */
            display: none;
        }
    </style>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">

                @auth
                    @if($policyChanged)
                        <div class="card mb-3">
                            <p class="card-body mb-0">
                                {!! __('privacy.we-changed') !!}
                            </p>
                        </div>
                    @elseif(!$hasUserSigned)
                        <div class="card mb-3">
                            <p class="card-body mb-0">
                                {!! __('privacy.not-signed-yet') !!}
                            </p>
                        </div>
                    @endif

                    @if(!$hasUserSigned)
                        <form method="POST" action="{{ route('gdpr.ack') }}" class="fixed-bottom text-end"
                              style="background-color: hsl(216, 25%, 95.1%);" id="form-privacy">
                            @csrf
                            <input type="hidden" name="valid_at" value="{{ $agreement->valid_at->toIso8601String() }}"/>
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-md-8 col-lg-7 my-2">
                                        <a class="btn btn-link pr-0" href="javascript:void(0)" role="button"
                                           data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                                            {{ __('settings.delete-account') }}
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            {{__('privacy.sign')}}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <button type="submit" class="btn btn-success btn-block" form="form-privacy">
                            {{__('privacy.sign.more')}}
                        </button>
                        <a class="btn btn-block btn-outline-secondary pr-0" href="javascript:void(0)" role="button"
                           data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                            {{ __('settings.delete-account.more') }}
                        </a>
                        <hr/>

                        @include('settings.modals.deleteUserModal')
                    @endif
                @endauth

                <div class="privacy">
                    @if(app()->getLocale() == 'de')
                        {!! Markdown::parse($agreement->body_md_de) !!}
                    @else
                        {!! Markdown::parse($agreement->body_md_en) !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
