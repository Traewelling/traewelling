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
                    <table id="my-table-id" class="table table-dark table-borderless table-hover table-responsive-lg m-0">
                        <thead>
                            <tr>
                                <th>Stop</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        @foreach($stopovers as $stop)
                            <tr data-ibnr="{{$stop['stop']['id']}}">
                                <td>{{ $stop['stop']['name'] }}</td>
                                <td>{{ __('arr') }} {{ date('H:i', strtotime($stop['arrival'])) }} @if(isset($stop['arrivalDelay']))<small>(+{{ $stop['arrivalDelay']/60 }})</small>@endif<br>
                                {{ __('dep') }} {{ date('H:i', strtotime($stop['departure'])) }} @if(isset($stop['departureDelay']))<small>(+{{ $stop['departureDelay']/60 }})</small>@endif</td>
                                <td>{{ $stop['departurePlatform'] }}</td>
                            </tr>
                            @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
