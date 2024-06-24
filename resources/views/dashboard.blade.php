@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                @if(session()->has('checkin-collision'))
                    <div class="alert alert-danger" id="checkin-collision-alert">
                        <h2 class="fs-4">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            {{__('overlapping-checkin')}}
                        </h2>

                        {{__('overlapping-checkin.description', ['lineName' => session()->get('checkin-collision')['lineName']])}}
                        {{__('overlapping-checkin.description2')}}
                        {{__('no-points-warning')}}

                        <hr/>

                        <form method="POST" action="{{route('trains.checkin')}}">
                            @csrf
                            <input type="hidden" name="force" value="true"/>
                            <input type="hidden" name="tripID"
                                   value="{{session()->get('checkin-collision')['validated']['tripID']}}"/>
                            <input type="hidden" name="start"
                                   value="{{session()->get('checkin-collision')['validated']['start']}}"/>
                            <input type="hidden" name="departure"
                                   value="{{session()->get('checkin-collision')['validated']['departure']}}"/>
                            <input type="hidden" name="destination"
                                   value="{{session()->get('checkin-collision')['validated']['destination']}}"/>
                            <input type="hidden" name="arrival"
                                   value="{{session()->get('checkin-collision')['validated']['arrival']}}"/>
                            <input type="hidden" name="body"
                                   value="{{session()->get('checkin-collision')['validated']['body'] ?? ''}}"/>
                            <input type="hidden" name="business_check"
                                   value="{{session()->get('checkin-collision')['validated']['business_check']}}"/>
                            <input type="hidden" name="checkinVisibility"
                                   value="{{session()->get('checkin-collision')['validated']['checkinVisibility']}}"/>
                            @isset(session()->get('validated')['tweet_check'])
                                <input type="hidden" name="tweet_check"
                                       value="{{session()->get('checkin-collision')['validated']['tweet_check']}}"/>
                            @endif
                            @isset(session()->get('validated')['toot_check'])
                                <input type="hidden" name="toot_check"
                                       value="{{session()->get('checkin-collision')['validated']['toot_check']}}"/>
                            @endif
                            <input type="hidden" name="event"
                                   value="{{session()->get('checkin-collision')['validated']['event'] ?? ''}}"/>

                            <div class="d-grid gap-2">
                                <button class="btn btn-success" type="submit">
                                    <i class="fa-solid fa-check"></i>
                                    {{__('overlapping-checkin.force-yes')}}
                                </button>
                                <button class="btn btn-secondary" type="button"
                                        onclick="$('#checkin-collision-alert').remove()">
                                    <i class="fa-solid fa-xmark"></i>
                                    {{__('overlapping-checkin.force-no')}}
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                @if(auth()->user()->hasRole('open-beta'))
                    <div id="station-board-new">
                        <Stationautocomplete :dashboard="true" :show-gps-button="true"></Stationautocomplete>
                    </div>
                @else
                    @include('includes.station-autocomplete')
                @endif
                @if($future->count() >= 1)
                    <div class="accordion accordion-flush" id="accordionFutureCheckIns">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingOne">
                                <button class="accordion-button collapsed"
                                        type="button"
                                        data-mdb-toggle="collapse"
                                        data-mdb-target="#future-check-ins"
                                        aria-expanded="false"
                                        aria-controls="future-check-ins"
                                >
                                    {{ __('dashboard.future') }}
                                </button>
                            </h2>
                            <div id="future-check-ins"
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

                @if(config('trwl.year_in_review.alert'))
                    <div class="alert alert-info">
                        <h4 class="alert-heading">
                            <i class="fa-solid fa-champagne-glasses"></i>
                            Träwelling {{__('year-review')}}
                        </h4>
                        <p>{{__('year-review.teaser')}}</p>
                        <a class="btn btn-outline-primary btn-block" href="/your-year/">
                            <i class="fa-solid fa-arrow-pointer text-primary"></i>
                            {{__('year-review.open')}}
                        </a>
                    </div>
                @endif

                @include('includes.statuses', ['statuses' => $statuses, 'showDates' => true])
                {{ $statuses->links() }}

                @include('includes.edit-modal')
                @include('includes.delete-modal')
            </div>
        </div>
    </div>
@endsection
