@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @include('includes.station-autocomplete')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                @if($future->count() >= 1)
                    <div class="accordion accordion-flush" id="accordionFutureCheckIns">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingOne">
                                <button
                                        class="accordion-button collapsed"
                                        type="button"
                                        data-mdb-toggle="collapse"
                                        data-mdb-target="#future-check-ins"
                                        aria-expanded="false"
                                        aria-controls="future-check-ins"
                                >
                                    {{ __('dashboard.future') }}
                                </button>
                            </h2>
                            <div
                                    id="future-check-ins"
                                    class="accordion-collapse collapse"
                                    aria-labelledby="flush-headingOne"
                                    data-mdb-parent="#accordionFutureCheckIns"
                            >
                                <div class="accordion-body">
                                    @include('includes.statuses', ['statuses' => $future, 'showDates' => false])
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @include('includes.statuses', ['statuses' => $statuses, 'showDates' => true])
            </div>
        </div>
        {{ $statuses->links() }}

        @include('includes.edit-modal')
        @include('includes.delete-modal')
    </div>
@endsection
