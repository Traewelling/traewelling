@extends('layouts.app')

@section('content')
    <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    @if (file_exists(public_path('img/'.$train['category'].'.svg')))
                        <img class="product-icon" src="{{ asset('img/'.$train['category'].'.svg') }}">
                    @else
                        <i class="fa fa-train"></i>
                    @endif
                    {{ $train['linename'] }} <i class="fas fa-arrow-alt-circle-right"></i> {{$destination}}
                </div>

                <div class="card-body p-0">
                    <table id="my-table-id" class="table table-dark table-borderless table-hover table-responsive-lg m-0" data-linename="{{ $train['linename'] }}" data-startname="{{ $start }}" data-start="{{ request()->start }}" data-tripid="{{ request()->tripID }}">
                        <thead>
                            <tr>
                                <th>{{__('stationboard.stopover')}}</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        @foreach($stopovers as $stop)
                            @if(@$stop['cancelled'] == 'true')
                                <tr>
                                    <td>{{ $stop['stop']['name'] }}</td>
                                    <td><span class="text-danger">{{ __('Stop cancelled') }}</span></td>
                                    <td>{{ $stop['departurePlatform'] }}</td>
                            @else
                                <tr class="train-destinationrow" data-ibnr="{{$stop['stop']['id']}}" data-stopname="{{$stop['stop']['name']}}">
                                <td>{{ $stop['stop']['name'] }}</td>
                                <td>@if($stop['arrival'] != null)
                                        {{ __('stationboard.arr') }} {{ date('H:i', strtotime($stop['arrival'])) }}{{__('dates.--uhr')}} @if(isset($stop['arrivalDelay']))<small>(+{{ $stop['arrivalDelay']/60 }})</small>@endif
                                    @endif<br>
                                    @if($stop['departure'] != null)
                                        {{ __('stationboard.dep') }} {{ date('H:i', strtotime($stop['departure'])) }}{{__('dates.--uhr')}} @if(isset($stop['departureDelay']))<small>(+{{ $stop['departureDelay']/60 }})</small>@endif
                                    @endif
                                </td>
                                <td>{{ $stop['departurePlatform'] }}</td>
                            @endif
                            </tr>
                            @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="modal fade" id="checkinModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{__('stationboard.new-checkin')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('trains.checkin') }}" method="POST" id="checkinForm">
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">{{__('stationboard.label-message')}}</label>
                            <textarea name="body" class="form-control" id="message-text"></textarea>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="tweet_check" name="tweet_check">
                            <label class="custom-control-label" for="tweet_check">{{__('stationboard.check-tweet')}}</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="toot_check" name="toot_check">
                            <label class="custom-control-label" for="toot_check">{{__('stationboard.check-toot')}}</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="business_check" name="business_check">
                            <label class="custom-control-label" for="business_check">{{__('stationboard.check-business')}}</label>
                        </div>
                        <input type="hidden" id="input-tripID" name="tripID" value="">
                        <input type="hidden" id="input-destination" name="destination" value="">
                        <input type="hidden" id="input-start" name="start" value="">
                        @csrf
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('menu.abort') }}</button>
                    <button type="button" class="btn btn-primary" id="checkinButton">{{ __('stationboard.btn-checkin') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
