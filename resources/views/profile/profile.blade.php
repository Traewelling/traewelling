@extends('layouts.app')

@section('title', $user->name)
@section('canonical', route('profile', ['username' => $user->username]))

@if($user->prevent_index)
    @section('meta-robots', 'noindex')
@else
    @section('meta-description', __('description.profile', [
        'username' => $user->name,
        'kmAmount' => number($user->train_distance / 1000, 0),
        'hourAmount' => number($user->train_duration / 60, 0)
    ]))
@endif

@section('content')
    @php /** @var \App\Models\User $user */ @endphp
    <div class="px-md-4 py-md-5 pt-2 pb-0 mt-n4 profile-banner">
        <div class="container">
            <img alt="{{ __('settings.picture') }}"
                 src="{{ \App\Http\Controllers\Backend\User\ProfilePictureController::getUrl($user) }}"
                 class="float-end img-thumbnail rounded-circle img-fluid profile-picture"/>
            <div class="text-white px-md-4">
                <h1 class="card-title h1-responsive font-bold mb-0 profile-name">
                    <strong>
                        {{ $user->name }}
                        @if($user->private_profile)
                            <i class="fas fa-user-lock"></i>
                        @endif
                    </strong>
                </h1>
                <span
                    class="d-flex flex-column flex-md-row justify-content-md-start align-items-md-center gap-md-2 gap-1 pt-1 pb-2 pb-md-0 small">
                    <small class="font-weight-light profile-tag">
                        {{ '@'. $user->username }}
                        @if($user->followedBy)
                            <span class="badge text-bg-success">
                                {{__('profile.follows-you')}}
                            </span>
                        @endif
                    </small>
                </span>
                <div class="d-flex py-3 flex-row justify-content-md-start align-items-md-center gap-1 ">
                    @include('profile.partials.actions')
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        @include('profile.partials.body')
    </div>
@endsection
