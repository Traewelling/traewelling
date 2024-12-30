@extends('layouts.app')

@section('title', __('trip-info.title', ['linename' => $trip->linename, 'date' => $trip->departure->format('d.m.Y')]))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>{{__('trip-info.title', ['linename' => $trip->linename, 'date' => $trip->departure->format('d.m.Y')])}}</h1>

                <div class="alert alert-info">
                    Diese Seite gehört zu den experimentellen Features von Träwelling.
                    Daher sieht sie auch nicht schön aus, zeigt nur generische Infos und ist nirgends verlinkt.
                    Du kannst sie gerne verbessern und einen PullRequest schicken.
                </div>
            </div>

            <div class="col-md-7">
                <h2>{{__('trip-info.stopovers')}}</h2>

                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>{{__('trip-info.stopover')}}</th>
                            <th>{{__('trip-info.arrival')}}</th>
                            <th>{{__('trip-info.departure')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trip->stopovers as $stopover)
                            <tr>
                                <td>{{$stopover->station->name}}</td>
                                <td>{{$stopover->arrival->format('H:i')}}</td>
                                <td>{{$stopover->departure->format('H:i')}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($trip->checkins->count() > 0)
                <div class="col-md-5">
                    <h2>{{__('trip-info.in-this-connection')}}</h2>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>{{__('trip-info.user')}}</th>
                                <th>{{__('trip-info.origin')}}</th>
                                <th>{{__('trip-info.destination')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trip->checkins as $checkin)
                                @can('view', $checkin->status)
                                    <tr>
                                        <td>
                                            <a href="{{route('profile', ['username' => $checkin->user->username])}}">
                                                <img
                                                    src="{{\App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($checkin->user)}}"
                                                    alt="{{$checkin->user->name}}" style="max-height: 1em;"
                                                    class="avatar">
                                                {{$checkin->user->name}}
                                            </a>
                                        </td>
                                        <td>{{$checkin->originStopover->station->name}}</td>
                                        <td>{{$checkin->destinationStopover->station->name}}</td>
                                    </tr>
                                @endcan
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection
