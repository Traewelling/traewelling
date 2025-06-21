@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div id="station-board-new">
                    <Apialerts></Apialerts>
                    <Stationautocomplete :dashboard="true" :show-gps-button="true"></Stationautocomplete>
                </div>

                @if($future->count() >= 1)
                    <div class="accordion accordion-flush" id="accordionFutureCheckIns">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingOne">
                                <button class="accordion-button collapsed"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#future-check-ins"
                                        aria-expanded="false"
                                        aria-controls="future-check-ins"
                                >
                                    {{ __('dashboard.future') }}
                                </button>
                            </h2>
                            <div id="future-check-ins"
                                 class="accordion-collapse collapse"
                                 aria-labelledby="flush-headingOne"
                                 data-bs-parent="#accordionFutureCheckIns"
                            >
                                <div class="accordion-body px-0">
                                    @include('includes.statuses', ['statuses' => $future, 'showDates' => false])
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(config('trwl.year_in_review.alert'))
                    <div class="alert alert-info" role="region" aria-label="{{ __('year-review') }}">
                        <h4 class="alert-heading">
                            <i class="fa-solid fa-champagne-glasses" aria-hidden="true"></i>
                            Träwelling {{ __('year-review') }}
                        </h4>
                        <p>{{ __('year-review.teaser') }}</p>
                        <a class="btn btn-outline-primary btn-block" href="/your-year/">
                            <i class="fa-solid fa-arrow-pointer text-primary" aria-hidden="true"></i>
                            {{ __('year-review.open') }}
                        </a>
                    </div>
                @endif

                @include('includes.statuses', ['statuses' => $statuses, 'showDates' => true])
                {{ $statuses->links() }}

                @if($showGlobalButton)
                    <div class="alert alert-info" role="region" aria-label="{{ __('dashboard.empty') }}">
                        <h4 class="alert-heading">
                            <i class="fa-solid fa-binoculars" aria-hidden="true"></i>
                            {{ __('dashboard.empty') }}
                        </h4>
                        <p>{{ __('dashboard.empty.teaser') }}</p>
                        <p>
                            {{ __('dashboard.empty.discover1') }}
                            <a href="{{ route('statuses.active') }}">
                                {{ __('menu.active') }}
                            </a>
                            {{ __('dashboard.empty.discover3') }}.
                        </p>
                    </div>
                @endif

                @include('includes.edit-modal')
                @include('includes.delete-modal')
            </div>
        </div>
    </div>
@endsection
