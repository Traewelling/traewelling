@extends('layouts.app')

@section('title', $hafasTrip->linename . ' -> ' . $hafasTrip->destinationStation->name)

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">

                @isset($searchedStation)
                    <div class="alert alert-warning">
                        {!! __('warning-alternative-station', [
                            'newStation' => $startStation->name,
                            'searchedStation' => $searchedStation->name,
                        ]) !!}
                    </div>
                @endisset

                <div class="card">
                    <div class="card-header bg-dark text-white"
                         data-linename="{{ $hafasTrip->linename }}"
                         data-startname="{{ $hafasTrip->originStation->name }}"
                         data-start="{{ request()->start }}"
                         data-tripid="{{ $hafasTrip->trip_id }}"
                    >
                        <div class="float-end">
                            <a href="#" class="train-destinationrow text-white"
                               data-ibnr="{{$lastStopover->trainStation->ibnr}}"
                               data-stopname="{{$lastStopover->trainStation->name}}"
                               data-arrival="{{$lastStopover->arrival_planned ?? $lastStopover->departure_planned}}">
                                <i class="fa fa-fast-forward"></i>
                            </a>
                        </div>
                        @if (file_exists(public_path('img/' . $hafasTrip->category->value . '.svg')))
                            <img class="product-icon" src="{{ asset('img/' . $hafasTrip->category->value . '.svg') }}"/>
                        @else
                            <i class="fa fa-train"></i>
                        @endif
                        {{ $hafasTrip->linename }}
                        <i class="fas fa-arrow-alt-circle-right"></i>
                        {{$hafasTrip->destinationStation->name}}
                    </div>

                    <div class="card-body p-0 table-responsive">
                        <table class="table table-dark table-borderless table-hover m-0"
                               data-linename="{{ $hafasTrip->linename }}"
                               data-startname="{{ $hafasTrip->originStation->name }}"
                               data-start="{{ request()->start }}"
                               data-tripid="{{ $hafasTrip->trip_id }}"
                        >
                            <tbody>
                                @foreach($stopovers as $stopover)
                                    @if($stopover->isArrivalCancelled)
                                        <tr>
                                            <td>{{ $stopover->trainStation->name }}</td>
                                            <td>
                                                <span class="text-danger">{{ __('stationboard.stop-cancelled') }}</span><br/>&nbsp;
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="train-destinationrow"
                                            data-ibnr="{{$stopover->trainStation->ibnr}}"
                                            data-stopname="{{$stopover->trainStation->name}}"
                                            data-arrival="{{$stopover->arrival_planned ?? $stopover->departure_planned}}"
                                        >
                                            <td>{{ $stopover->trainStation->name }}</td>
                                            <td class="text-end">
                                                {{ __('stationboard.arr') }}
                                                {{ $stopover->arrival_planned->isoFormat(__('time-format'))}}
                                                @isset($stopover->arrival_real)
                                                    <small>(<span
                                                            class="traindelay">+{{ $stopover->arrival_real->diffInMinutes($stopover->arrival_planned) }}</span>)</small>
                                                @endisset
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
