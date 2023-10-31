@extends('layouts.app')

@section('title', html_entity_decode(__('privacy.title')))
@section('meta-robots', 'noindex')

@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">

                @auth
                    @if($user->privacy_ack_at == null)
                        <div class="card mb-3">
                            <p class="card-body mb-0">
                                {!! __('privacy.not-signed-yet') !!}
                            </p>
                        </div>
                    @elseif($user->privacy_ack_at->isBefore($agreement->valid_at))
                        <div class="card mb-3">
                            <p class="card-body mb-0">
                                {!! __('privacy.we-changed') !!}
                            </p>
                        </div>
                    @endif

                    @if(is_null($user->privacy_ack_at)||$agreement->valid_at->isAfter($user->privacy_ack_at))
                        <form method="POST" action="{{ route('gdpr.ack') }}" class="fixed-bottom text-end"
                              style="background-color: hsl(216, 25%, 95.1%);" id="form-privacy">
                            @csrf
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-md-8 col-lg-7 my-2">
                                        <a class="btn btn-link pr-0" href="javascript:void(0)" role="button"
                                           data-mdb-toggle="modal" data-mdb-target="#deleteUserModal">
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
                           data-mdb-toggle="modal" data-mdb-target="#deleteUserModal">
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
