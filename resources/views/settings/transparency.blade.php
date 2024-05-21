@extends('layouts.settings')
@section('title', __('settings.tab.transparency'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                {{__('activity.description')}}
                {{__('activity.description2')}}
                <hr />
                <i class="fa-regular fa-face-smile-beam"></i>
                {{__('beta')}}
            </div>

            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{__('activity.causer')}}</th>
                        <th>{{__('activity.action')}}</th>
                        <th>{{__('activity.subject')}}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                        <tr>
                            <td>
                                @if($activity->causer_id == auth()->id())
                                    <span class="text-success">{{ __('you') }}</span>
                                @elseif(in_array($activity->causer_id, $userIdsFromAdmins))
                                    <span class="text-danger">{{ __('admin') }}</span>
                                @endif
                            </td>
                            <td>{{ $activity->description }}</td>
                            <td>
                                @if($activity->subject_type === \App\Models\User::class)
                                    <span class="text-primary">Account</span>
                                @elseif($activity->subject_type === \App\Models\Checkin::class)
                                    <span class="text-primary">Check-in</span>
                                @elseif($activity->subject_type === \App\Models\Status::class)
                                    <a class="text-primary" href="/status/{{$activity->subject_id}}">
                                        Status {{$activity->subject_id}}
                                    </a>
                                @endif
                            </td>
                            <td>{{ $activity->created_at?->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $activities->links()}}

            <div class="alert alert-primary">
                <i class="fa-solid fa-eraser"></i>
                {{__('activity.deletion')}}
                {{__('activity.deletion-except')}}
            </div>
        </div>
    </div>
@endsection
