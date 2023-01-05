@extends('layouts.app')

@section('title', __('support.create'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                @if($emailAvailable)

                    <div class="alert alert-danger">
                        <b>{{__('support.go-to-github')}}</b>
                        <hr/>
                        <a href="https://github.com/Traewelling/traewelling/issues/new?assignees=&labels=bug%2CTo+Do&template=bug_report.md"
                           target="_blank" class="btn btn-sm btn-danger">
                            <i class="fa-solid fa-bug text-white" aria-hidden="true"></i>
                            {{__('report-bug')}}
                        </a>
                        <a href="https://github.com/Traewelling/traewelling/issues/new?assignees=&labels=enhancement&template=feature_request.md&title="
                           target="_blank" class="btn btn-sm btn-success">
                            <i class="fa-solid fa-plus text-white" aria-hidden="true"></i>
                            {{__('request-feature')}}
                        </a>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h1 class="fs-4 pb-2">{{__('support.create')}}</h1>

                            <form method="POST" action="{{route('support.submit')}}">
                                @csrf

                                <div class="form-outline mb-4">
                                    <input type="text" id="form-subject" class="form-control" name="subject"/>
                                    <label class="form-label" for="form-subject">{{__('subject')}}</label>
                                </div>

                                <div class="form-outline mb-4">
                                    <textarea class="form-control" id="form-message" name="message" rows="4"></textarea>
                                    <label class="form-label" for="form-message">{{__('how-can-we-help')}}</label>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block mb-4">
                                    {{__('support.submit')}}
                                </button>
                                <hr/>
                                <small>{{__('support.answer', ['address' => auth()->user()->email])}}</small>
                            </form>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h5 class="fw-bold"><i class="fas fa-user-shield"></i> {{__('support.privacy')}}</h5>
                        {{__('support.privacy.description')}}
                        {{__('support.privacy.description2')}}
                    </div>

                @else
                    <h4>{{__('support.create')}}</h4>
                    <hr/>
                    <div class="alert alert-danger">
                        <p>{{__('support.email-required')}}</p>
                        <a href="{{route('settings')}}" class="btn btn-sm btn-primary">
                            <i class="fas fa-user-cog"></i>
                            {{__('go-to-settings')}}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
